<?php

namespace App\Services;

use App\Dto\AnonymousCustomerDto;
use App\Dto\ChangeVisitDateDto;
use App\Dto\EmployeeVisitDto;
use App\Dto\PostponeVisitDto;
use App\Enum\BranchShiftEnum;
use App\Enum\EmployeeVisitStatusEnum;
use App\Enum\RecurrenceTypeEnum;
use App\Enum\ScheduleTypeEnum;
use App\Enum\SourceTypeEnum;
use App\Enum\VisitPurposeTypeEnum;
use App\Events\ManualVisitCreated;
use App\Events\VisitConfirmed;
use App\Events\VisitDateChanged;
use App\Events\VisitPostponed;
use App\Http\Filters\EmployeeVisitFilters;
use App\Http\Filters\ScheduleVisitFilter;
use App\Http\Resources\EmployeeVisitShowResource;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitOverride;
use App\Models\ScheduleVisit;
use App\Models\User;
use App\Notifications\DailyVisitReminderNotification;
use App\Notifications\MissedVisitNotification;
use Carbon\Carbon;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;

readonly class EmployeeVisitService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private VisitProjectionService $visitProjectionService,
        private ScheduleVisitService $scheduleVisitService,
        private AnonymousCustomerService $anonymousCustomerService,
        private EmployeeVisitCommentService $employeeVisitCommentService,
    ) {}

    public static function recordVisit(EmployeeVisit $visit): void
    {
        $location = session()->get('location');

        if ($location) {
            app()->get(self::class)->updateVisit($visit, [
                'status' => EmployeeVisitStatusEnum::VISITED->value,
                'lat'    => $location->lat,
                'lng'    => $location->lng,
            ]);
        }
    }

    public function updateVisit(EmployeeVisit $employeeVisit, array $data): EmployeeVisit
    {
        $isHighbaseCheckIn = $employeeVisit->isHighbaseBranchVisit()
            && ($data['status'] ?? null) === EmployeeVisitStatusEnum::VISITED->value;

        // Highbase Check In: set status to visited only (no confirmed_at); Confirm step is separate
        $payload = match ($data['status']) {
            'visited' => $isHighbaseCheckIn
                ? ['status' => EmployeeVisitStatusEnum::VISITED]
                : ['status' => EmployeeVisitStatusEnum::VISITED, 'confirmed_at' => now()],
            'checkout' => ['checkout_at' => now()],
            default    => [],
        };

        if ($data['status'] === EmployeeVisitStatusEnum::VISITED->value) {
            $visitData = $this->processVisitCoordinates($employeeVisit, $data);
            $payload   = array_merge($payload, $visitData);
        }
        $employeeVisit->update($payload);

        // Only fire VisitConfirmed when we actually set confirmed_at (non-Highbase or Highbase Confirm step)
        if (($data['status'] ?? null) === EmployeeVisitStatusEnum::VISITED->value && ! $isHighbaseCheckIn) {
            event(new VisitConfirmed($employeeVisit));
        }

        return $employeeVisit;
    }

    /**
     * Confirm a Highbase visit after Check In. Requires the visit to already have at least one
     * attachment (uploaded from the detail page). Processes location (first time: update store pin;
     * otherwise: haversine validation), sets confirmed_at and checkout_at.
     */
    public function confirmHighbaseVisit(EmployeeVisit $visit, float $lat, float $lng): EmployeeVisit
    {
        if (! $visit->isHighbaseBranchVisit()) {
            throw new AuthorizationException(__('This action is only for Highbase branch visits.'));
        }

        if ($visit->confirmed_at) {
            throw new DomainException(__('Visit is already confirmed.'));
        }

        if (! $visit->pm_name || (! $visit->pm_phone && ! $visit->pm_email)) {
            throw new DomainException(__('Please complete the Purchasing Manager information (name and phone or email) from the visit detail page before confirming.'));
        }

        if (! $visit->hasMedia('storefront_images')) {
            throw new DomainException(__('Please upload at least one storefront image from the visit detail page before confirming.'));
        }

        $visitData = $this->processVisitCoordinates($visit, ['lat' => $lat, 'lng' => $lng]);

        $visit->update([
            'confirmed_at'     => now(),
            'checkout_at'      => now(),
            'employee_lat'     => $visitData['employee_lat'] ?? $visit->employee_lat,
            'employee_lng'     => $visitData['employee_lng'] ?? $visit->employee_lng,
            'destination_lat'  => $visitData['destination_lat'] ?? $visit->destination_lat,
            'destination_lng'  => $visitData['destination_lng'] ?? $visit->destination_lng,
            'distance_covered' => $visitData['distance_covered'] ?? $visit->distance_covered,
        ]);

        event(new VisitConfirmed($visit));

        return $visit;
    }

    /**
     * Process visit coordinates and calculate distance
     */
    private function processVisitCoordinates(EmployeeVisit $employeeVisit, array $data): array
    {
        $isHighbaseBranch = $employeeVisit->isHighbaseBranchVisit();
        $address          = $employeeVisit->visitable->address ?? null;
        $employeeLat      = data_get($data, 'lat');
        $employeeLng      = data_get($data, 'lng');
        $storeLat         = data_get($address, 'pin_location.lat');
        $storeLng         = data_get($address, 'pin_location.lng');

        if ($isHighbaseBranch) {
            return $this->processHighbaseVisit($employeeVisit, $employeeLat, $employeeLng, (float) $storeLat, (float) $storeLng);
        }

        return $this->processRegularVisit($employeeLat, $employeeLng, $address, $storeLat, $storeLng);
    }

    /**
     * Process highbase branch visit - allows admin confirmation without coordinates
     */
    private function processHighbaseVisit(
        EmployeeVisit $employeeVisit,
        ?float $employeeLat,
        ?float $employeeLng,
        ?float $storeLat,
        ?float $storeLng
    ): array {

        if (! $employeeLat || ! $employeeLng) {
            return [
                'employee_lat'     => null,
                'employee_lng'     => null,
                'destination_lat'  => $storeLat,
                'destination_lng'  => $storeLng,
                'distance_covered' => 0,
            ];
        }

        // Store has no coordinates - set from employee location (first visit)
        if (! $storeLat || ! $storeLng) {
            $this->updateStoreCoordinates($employeeVisit->visitable, $employeeLat, $employeeLng);
        }

        try {
            $distance = haversineDistance(
                latitudeFrom: $employeeLat,
                longitudeFrom: $employeeLng,
                latitudeTo: $storeLat,
                longitudeTo: $storeLng
            );
        } catch (\Throwable) {
            $distance = null;
        }

        if ($distance > 500) {
            Session::flash('error', __('You are not at the location yet. You can continue the visit.'));
        }

        return [
            'employee_lat'     => $employeeLat,
            'employee_lng'     => $employeeLng,
            'destination_lat'  => $employeeLat,
            'destination_lng'  => $employeeLng,
            'distance_covered' => 0,
        ];

        //
        //        return [
        //            'employee_lat'     => $employeeLat,
        //            'employee_lng'     => $employeeLng,
        //            'destination_lat'  => $storeLat,
        //            'destination_lng'  => $storeLng,
        //            'distance_covered' => round($distance / 1000, 2),
        //        ];
    }

    /**
     * Process regular (non-highbase) branch visit - requires coordinates
     */
    private function processRegularVisit(
        ?float $employeeLat,
        ?float $employeeLng,
        ?array $address,
        ?float $storeLat,
        ?float $storeLng
    ): array {
        if (! $employeeLat || ! $employeeLng) {
            throw new DomainException(__('Unable to calculate distance — missing employee coordinates.'));
        }

        if ($address === null || $address === [] || ! $storeLat || ! $storeLng) {
            throw new DomainException(__('Customer store address is not confirmed. Please verify the location before marking the visit as visited.'));
        }

        $distance = haversineDistance(
            latitudeFrom: $employeeLat,
            longitudeFrom: $employeeLng,
            latitudeTo: $storeLat,
            longitudeTo: $storeLng
        );

        if ($distance > 500) {
            throw new DomainException(__('You are too far from the customer location to mark this visit as visited.'));
        }

        return [
            'employee_lat'     => $employeeLat,
            'employee_lng'     => $employeeLng,
            'destination_lat'  => $storeLat,
            'destination_lng'  => $storeLng,
            'distance_covered' => round($distance / 1000, 2),
        ];
    }

    /**
     * Update store coordinates from employee's current location
     */
    private function updateStoreCoordinates($visitable, float $lat, float $lng): void
    {
        if (! $visitable) {
            return;
        }

        $address                 = $visitable->address ?? [];
        $address['pin_location'] = [
            'lat' => $lat,
            'lng' => $lng,
        ];

        $visitable->update(['address' => $address]);
    }

    /**
     * Store uploaded visit attachments to the media library.
     */
    public function storeVisitAttachments(EmployeeVisit $visit, array $attachments): void
    {
        foreach ($attachments as $file) {
            $visit
                ->addMedia($file)
                ->withCustomProperties(['attached_by' => auth()->id()])
                ->toMediaCollection('visit_attachments');
        }
    }

    /**
     * Save Highbase visit purchasing manager data and delivery status.
     */
    public function saveHighbaseVisitData(
        EmployeeVisit $visit,
        array $data,
        array $newImageIds = [],
        array $keepImageIds = []
    ): EmployeeVisit {
        $shipmentDelivered = $data['shipment_delivered'] ?? null;

        $reason = ($data['shipment_not_delivered_reason'] ?? null) ?: null;

        if ($shipmentDelivered === null || $shipmentDelivered) {
            $reason = null;
        }

        $visit->update([
            'pm_name'                       => $data['pm_name'],
            'pm_phone'                      => $data['pm_phone'] ?? null,
            'pm_email'                      => $data['pm_email'] ?? null,
            'shipment_delivered'            => $data['shipment_delivered'],
            'shipment_not_delivered_reason' => $reason,
            'shipment_not_delivered_other'  => ($data['shipment_not_delivered_reason'] ?? '') === 'other'
                ? ($data['shipment_not_delivered_other'] ?? null)
                : null,
        ]);

        $this->syncStorefrontImages($visit, $keepImageIds, $newImageIds);

        return $visit;
    }

    /**
     * Remove storefront images not in $keepImageIds, then import any new chunked uploads.
     */
    private function syncStorefrontImages(
        EmployeeVisit $visit,
        array $keepImageIds,
        array $newImageIds
    ): void {
        $keepIds = array_map('intval', $keepImageIds);

        $visit->getMedia('storefront_images')
            ->filter(fn ($media): bool => ! in_array($media->id, $keepIds, true))
            ->each(fn ($media) => $media->delete());

        foreach ($newImageIds as $serverId) {
            $this->importChunkedStorefrontImage($visit, (string) $serverId);
        }
    }

    /**
     * Move a single chunked-upload temp file into the storefront_images media collection.
     * $serverId is the uniqid() token returned by HandleChunkUploadsController.
     */
    private function importChunkedStorefrontImage(EmployeeVisit $visit, string $serverId): void
    {
        try {
            $files = Storage::files("chunked_attachments/{$serverId}");

            if (empty($files)) {
                return;
            }

            $visit
                ->addMediaFromDisk($files[0])
                ->withCustomProperties(['attached_by' => auth()->id()])
                ->usingFileName(basename($files[0]))
                ->toMediaCollection('storefront_images');
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function postponeVisit(EmployeeVisit $visit, PostponeVisitDto $dto): EmployeeVisit
    {

        if ($visit->exists) {
            $newVisit = DB::transaction(function () use ($visit, $dto) {

                $visit->update([
                    'status'          => EmployeeVisitStatusEnum::POSTPONED,
                    'postpone_reason' => $dto->reason,
                    'postpone_notes'  => $dto->notes,
                ]);

                $this->employeeVisitCommentService
                    ->addComment($visit, __(
                        'Visit was postponed due to :reason, Additional notes: :notes',
                        ['notes' => $dto->notes, 'reason' => $dto->reason->label()]
                    ));

                $newVisit = $visit->replicate([
                    'status',
                    'postpone_reason',
                    'postpone_notes',
                ]);

                $newVisit->assignStatus(EmployeeVisitStatusEnum::SCHEDULED);
                $newVisit->assignScheduledAt($visit->scheduled_at->addDay());
                $newVisit->assignCreator($dto->user->id);
                $newVisit->assignParent($visit);
                $newVisit->save();

                return $newVisit;
            });

            event(
                new VisitPostponed(
                    $visit,
                    $visit->scheduled_at->toDateString(),
                    $visit->scheduled_at->addDay()->toDateString(),
                    $dto->reason->label()
                )
            );

            return $newVisit;
        }

        $this->visitProjectionService->applyPostponeOverride($visit, $dto);

        return $visit;
    }

    public function generateVisitForDate(int $scheduleId, string $date): ?EmployeeVisit
    {
        $schedule = ScheduleVisit::findOrFail($scheduleId);
        $day      = Carbon::parse($date);

        // Always resolve any override first (e.g., postponed or rescheduled)
        $day = $this->resolveOverrideDate($schedule, $day);

        // Handle recurrence type based on schedule_type
        $match = false;

        if ($schedule->schedule_type === ScheduleTypeEnum::RECURRING) {
            $match = $this->matchesRecurrenceRule($schedule, $day);
        } elseif ($schedule->schedule_type === ScheduleTypeEnum::ONE_TIME) {
            $match = $schedule->one_time_date === $day->toDateString();
        }

        if (! $match) {
            return null;
        }

        // Avoid duplicates
        if ($this->alreadyExists($schedule, $day)) {
            return null;
        }

        // Finally, create the visit
        return $this->createVisitFromSchedule($schedule, $day);
    }

    private function resolveOverrideDate(ScheduleVisit $schedule, Carbon $day): Carbon
    {
        // Step 1: Check if a future override exists (after or on this date)
        $futureOverride = EmployeeVisitOverride::query()
            ->where('schedule_visit_id', $schedule->id)
            ->where('status', EmployeeVisitStatusEnum::SCHEDULED)
            ->whereDate('visit_date', '>=', $day->toDateString())
            ->orderBy('visit_date')
            ->first();

        // Step 2: If found, return that override’s new date
        if ($futureOverride) {
            return $futureOverride->visit_date->startOfDay();
        }

        // Step 3: Otherwise, follow recurrence (no override affects it)
        return $day->startOfDay();
    }

    private function matchesRecurrenceRule(ScheduleVisit $schedule, Carbon $day): bool
    {
        /**
         * Determine whether the given date ($day) aligns with the schedule's recurrence rule.
         * The recurrence rule is defined by $schedule->recurrence_type and $schedule->recurrence_value.
         *
         * Examples:
         * - WEEKLY or BIWEEKLY: recurrence_value = 1 (Monday), 2 (Tuesday), etc.
         *   e.g., if recurrence_value = 1 and $day is Monday, it matches.
         *
         * - MONTHLY: recurrence_value = 15 means every month on the 15th.
         *   e.g., if $day = 2025-10-15, it matches.
         *
         * - DURATION: recurrence_value = 3 means every 3 days starting from start_date.
         *   e.g., if start_date = 2025-10-01 and $day = 2025-10-04 (3 days later), it matches.
         */

        return match ($schedule->recurrence_type) {
            // WEEKLY or BIWEEKLY recurrence:
            // Checks if the weekday number of $day matches the recurrence_value.
            // Example: recurrence_value = 1 (Monday), $day is a Monday → matches.
            //          recurrence_value = 3 (Wednesday), $day is a Wednesday → matches.
            RecurrenceTypeEnum::WEEKLY,
            RecurrenceTypeEnum::BIWEEKLY => $day->dayOfWeek === (int) $schedule->recurrence_value,

            // MONTHLY recurrence:
            // Checks if the day of the month of $day matches the recurrence_value.
            // Example: recurrence_value = 15, $day is the 15th of the month → matches.
            //          recurrence_value = 1, $day is the 1st of the month → matches.
            RecurrenceTypeEnum::MONTHLY => $day->day === (int) $schedule->recurrence_value,

            // DURATION-based recurrence:
            // Checks if the number of days between start_date and $day is divisible by recurrence_value.
            // Example: start_date = 2025-10-01, recurrence_value = 3
            //          $day = 2025-10-04 → diffInDays = 3 → 3 % 3 == 0 → matches.
            //          $day = 2025-10-07 → diffInDays = 6 → 6 % 3 == 0 → matches.
            RecurrenceTypeEnum::DURATION => Carbon::parse($schedule->start_date)
                ->diffInDays($day) % (int) $schedule->recurrence_value === 0,

            default => false,
        };
    }

    private function alreadyExists(ScheduleVisit $schedule, Carbon $day): bool
    {
        return EmployeeVisit::query()
            ->where('source_type', SourceTypeEnum::SCHEDULE)
            ->where('status', EmployeeVisitStatusEnum::PENDING)
            ->where('schedule_visit_id', $schedule->id)
            ->whereDate('scheduled_at', $day->toDateString())
            ->exists();
    }

    private function createVisitFromSchedule(ScheduleVisit $schedule, Carbon $day): EmployeeVisit
    {
        $weight = $this->calculateWeight($schedule);

        return EmployeeVisit::create([
            'employee_id'       => $schedule->employee_id,
            'visitable_id'      => $schedule->visitable_id,
            'visitable_type'    => $schedule->visitable_type,
            'status'            => EmployeeVisitStatusEnum::PENDING,
            'scheduled_at'      => $day,
            'source_type'       => SourceTypeEnum::SCHEDULE,
            'schedule_visit_id' => $schedule->id,
            'custom_weight'     => 0,
            'weight'            => $weight,
            'created_by'        => $schedule->created_by,
            'purpose'           => $schedule->purpose,
            'branch_id'         => $schedule->branch_id,
        ]);
    }

    private function calculateWeight(ScheduleVisit $schedule): int
    {
        $distanceWeight = 1;

        $shiftWeight = match ($schedule->visitable?->shiftType()) {
            BranchShiftEnum::SINGLE_SHIFT   => 2,
            BranchShiftEnum::MULTIPLE_SHIFT => 3,
            default                         => 0,
        };

        $purposeWeight = match ($schedule->purpose) {
            VisitPurposeTypeEnum::ORDER_DELIVERY => 2,
            default                              => 0,
        };

        return $distanceWeight + $shiftWeight + $purposeWeight;
    }

    public function getTodayStats(EmployeeVisitFilters $filters): array
    {
        $filters->load();

        $query = $filters->execute(function ($query): void {
            $query->whereDate('scheduled_at', today());
        })->query();

        $visited   = EmployeeVisitStatusEnum::VISITED->value;
        $pending   = EmployeeVisitStatusEnum::PENDING->value;
        $baseQuery = $query->getQuery();
        $baseQuery->select([
            DB::raw(
                'COUNT(*) as total_points,
                 SUM(status = ?) as completed_points,
                 SUM(status = ?) as pending_points,
                 SUM(CASE WHEN status = ? THEN distance_covered ELSE 0 END) as total_distance_covered'
            ),
        ]);
        $baseQuery->addBinding([$visited, $pending, $visited], 'select');

        $stats = $query->first();

        return [
            'total_points'     => (int) $stats->total_points,
            'completed_points' => (int) $stats->completed_points,
            'pending_points'   => (int) $stats->pending_points,
            'distance_covered' => round((float) $stats->total_distance_covered, 2),
        ];
    }

    public function getTodayPaginatedResults(
        EmployeeVisitFilters $query,
        User $user,
        ?float $lng,
        ?float $lat
    ): LengthAwarePaginator {
        $branch              = currentBranch();
        $hasDistanceOrdering = $lat !== null && $lng !== null;

        return $query->execute(
            function (QueryBuilder $builder) use ($branch, $hasDistanceOrdering) {
                $builder
                    ->when(config('highbase.branch_id') == $branch->id, function ($query) {
                        $query->whereBetween('scheduled_at',
                            [now()->subDay()->toDateString(), today()->addDay()->toDateString()]);
                    })
                    ->when(config('highbase.branch_id') != $branch->id, function ($query) {
                        $query->whereDate('scheduled_at', Carbon::today()->toDateString());
                    })
                    ->withExists('orders as is_invoiced')
                    ->withCount('orders')
                    ->with([
                        'orders' => fn ($q) => $q->latest(),
                        'employee.user',
                        'visitable' => function ($morph): void {
                            $morph->morphWith([
                                Branch::class                  => ['addresses'],
                                AnonymousCustomerBranch::class => ['customer'],
                            ]);
                        },
                        'schedule',
                        'comments.employee.user',
                        'lines' => function ($q): void {
                            $q->with('orderLine', function ($lineQuery): void {
                                $lineQuery
                                    ->select([
                                        'id',
                                        'order_id',
                                        'product_id',
                                        'quantity',
                                        'currency',
                                        'packaging',
                                        'price',
                                        'total',
                                        'status',
                                        'variant_id',
                                    ])
                                    ->with(['variant:id,name,image', 'order:id,uuid']);
                            });
                        },
                    ])
                    ->when(! $hasDistanceOrdering, function ($q) {
                        $q->orderByRaw('(weight + custom_weight) DESC')
                            ->orderBy('id');
                    });
            }
        )->paginate(15);
    }

    public function getTimelinePaginatedResults(
        User $user,
        array $filters,
        ScheduleVisitFilter $query,
        Branch $branch,
    ): Collection {

        // Parse array filters (comma-separated strings from query params)
        $employeeIds  = $this->parseArrayFilter($filters['employee_id'] ?? null);
        $customerIds  = $this->parseArrayFilter($filters['customer_id'] ?? null);
        $visitableIds = $this->parseArrayFilter($filters['visitable_id'] ?? null);

        // Support legacy single values
        $employee = $employeeIds && count($employeeIds) === 1 ? $employeeIds[0] : null;
        $customer = $visitableIds && count($visitableIds) === 1 ? $visitableIds[0] : null;

        $from = Carbon::parse($filters['date_from'] ?? now());
        $to   = Carbon::parse($filters['date_to'] ?? now()->addMonths(3));

        $schedules = $query
            ->applyRoleBasedVisibility($user, $branch)
            ->execute(function (QueryBuilder $builder) use ($customerIds, $visitableIds): void {
                if ($customerIds && count($customerIds) > 0) {
                    $builder->whereIn('visitable_id', $customerIds);
                } elseif ($visitableIds && count($visitableIds) > 0) {
                    $builder->whereIn('visitable_id', $visitableIds);
                }
            })
            ->get();

        $visits = $this->projectVisitsFromSchedules($schedules, $from, $to);

        $fromDate = Carbon::parse($from)->toDateString();
        $toDate   = Carbon::parse($to)->toDateString();

        if ($schedules->isEmpty() && empty($filters['recurrence_type'])) {
            $visits = $this->getTimelineManualVisits($branch, $fromDate, $toDate, $employeeIds, $employee, $customerIds,
                $visitableIds, $customer);
        } else {
            $manualVisits = $this->getTimelineManualVisits($branch, $fromDate, $toDate, $employeeIds, $employee,
                $customerIds, $visitableIds, $customer);
            $visits = $visits->concat($manualVisits)->unique('id')->values();
        }

        $blockNumber = $filters['block_number'] ?? null;
        $state       = $filters['state'] ?? null;
        $city        = $filters['city'] ?? null;
        $roadStreet  = $filters['road_street'] ?? null;

        // Apply conditional filters to include manual visits as well
        $filtered = $visits
            ->when(
                $employeeIds && count($employeeIds) > 0,
                fn ($c) => $c->filter(fn ($v): bool => in_array($v->employee_id, $employeeIds))
            )
            ->when($employee && ! $employeeIds, fn ($c) => $c->where('employee_id', $employee))
            ->when(
                $customerIds && count($customerIds) > 0,
                fn ($c) => $c->filter(fn ($v): bool => in_array($v->visitable_id, $customerIds))
            )
            ->when(
                $visitableIds && count($visitableIds) > 0,
                fn ($c) => $c->filter(fn ($v): bool => in_array($v->visitable_id, $visitableIds))
            )
            ->when($customer && ! $customerIds && ! $visitableIds, fn ($c) => $c->where('visitable_id', $customer))
            ->filter(function ($v) use ($fromDate, $toDate): bool {
                $visitDate = Carbon::parse($v->scheduled_at)->toDateString();

                return $visitDate >= $fromDate && $visitDate <= $toDate;
            })
            ->when($blockNumber, fn ($c) => $c->filter(function ($v) use ($blockNumber): bool {
                $address = data_get($v, 'visitable.address');

                if (! $address || ! is_array($address)) {
                    return false;
                }
                $visitBlockNumber = data_get($address, 'block_number');

                return $visitBlockNumber && stripos($visitBlockNumber, (string) $blockNumber) !== false;
            }))
            ->when($state, function ($c) use ($state) {
                $stateIds = $this->parseArrayFilter($state);

                if ($stateIds === null || $stateIds === []) {
                    return $c;
                }

                return $c->filter(function ($v) use ($stateIds): bool {
                    $address = data_get($v, 'visitable.address');

                    if (! $address || ! is_array($address)) {
                        return false;
                    }
                    $visitStateId = data_get($address, 'state_id') ?? data_get($address, 'state');

                    if ($visitStateId === null) {
                        return false;
                    }
                    $visitStateId = (int) $visitStateId;

                    return in_array($visitStateId, $stateIds, true);
                });
            })
            ->when($city, function ($c) use ($city) {
                $cityIds = $this->parseArrayFilter($city);

                if ($cityIds === null || $cityIds === []) {
                    return $c;
                }

                return $c->filter(function ($v) use ($cityIds): bool {
                    $address = data_get($v, 'visitable.address');

                    if (! $address || ! is_array($address)) {
                        return false;
                    }
                    $visitCityId = data_get($address, 'city_id') ?? data_get($address, 'city');

                    if ($visitCityId === null) {
                        return false;
                    }
                    $visitCityId = (int) $visitCityId;

                    return in_array($visitCityId, $cityIds, true);
                });
            })
            ->when($roadStreet, fn ($c) => $c->filter(function ($v) use ($roadStreet): bool {
                $address = data_get($v, 'visitable.address');

                if (! $address || ! is_array($address)) {
                    return false;
                }
                $visitRoadStreet = data_get($address, 'road_street');

                return $visitRoadStreet && (string) $visitRoadStreet === (string) $roadStreet;
            }))
            ->when(
                $branch->isHighbaseBranch() && ! empty($filters['visit_status']),
                fn ($c) => $c->filter(function ($v) use ($filters): bool {
                    $status = $v->status;
                    $value  = $status?->value ?? $status;

                    return $value === $filters['visit_status'];
                })
            );

        return $filtered->values();
    }

    /**
     * Fetch manual visits for the timeline date range and filters.
     * Used when there are no schedules, or when branch is highbase (catalog visits have no schedule).
     */
    private function getTimelineManualVisits(
        Branch $branch,
        string $fromDate,
        string $toDate,
        ?array $employeeIds,
        $employee,
        ?array $customerIds,
        ?array $visitableIds,
        $customer
    ): Collection {
        $query = EmployeeVisit::query()
            ->where('branch_id', $branch->id)
            ->with(['employee.user', 'visitable', 'comments.employee.user'])
            ->whereDate('scheduled_at', '>=', $fromDate)
            ->whereDate('scheduled_at', '<=', $toDate)
            ->where('source_type', SourceTypeEnum::MANUAL->value);

        if ($employeeIds && count($employeeIds) > 0) {
            $query->whereIn('employee_id', $employeeIds);
        } elseif ($employee !== null) {
            $query->where('employee_id', $employee);
        }

        if ($customerIds && count($customerIds) > 0) {
            $query->whereIn('visitable_id', $customerIds);
        } elseif ($visitableIds && count($visitableIds) > 0) {
            $query->whereIn('visitable_id', $visitableIds);
        } elseif ($customer !== null) {
            $query->where('visitable_id', $customer);
        }

        return $query->get();
    }

    /**
     * Parse array filter from comma-separated string or array
     */
    private function parseArrayFilter($value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return array_filter(array_map(intval(...), $value));
        }

        if (is_string($value) && str_contains($value, ',')) {
            return array_filter(array_map(intval(...), explode(',', $value)));
        }

        return [intval($value)];
    }

    private function projectVisitsFromSchedules(Collection $schedules, string $from, string $to): Collection
    {
        return $schedules->flatMap(fn ($schedule): Collection => $this->visitProjectionService
            ->getProjectedVisitsForSchedule(
                scheduleId: $schedule->id,
                startDate: $from,
                endDate: $to
            ))->unique(
                fn ($visit): string => $visit->scheduled_at->toDateString().'-'.
                    $visit->employee_id.'-'.
                    $visit->visitable_id.'-'.
                    $visit->status->name.'-'.
                    $visit->source_type->name
            )->values();
    }

    public function changeVisitDate(EmployeeVisit $visit, ChangeVisitDateDto $dto): EmployeeVisit
    {
        if ($dto->changeParent && $visit->schedule_visit_id) {
            $this->updateParentSchedule($visit->schedule_visit_id, $dto);

            return $visit;
        }

        return $visit->exists
            ? $this->rescheduleExistingVisit($visit, $dto)
            : $this->visitProjectionService->rescheduleFutureVisit($visit, $dto);
    }

    private function updateParentSchedule(int $scheduleId, ChangeVisitDateDto $dto): void
    {
        ScheduleVisit::findOrFail($scheduleId)->update([
            'recurrence_type'  => $dto->recurrenceType,
            'recurrence_value' => $dto->recurrenceValue,
            'start_date'       => $dto->newDate,
        ]);
    }

    /**
     * Reschedule an existing persisted visit.
     *
     * Handles two types of visits:
     * 1. Missed visits:
     *    - Existing visit remains untouched.
     *    - Only a new scheduled visit is created with the new date.
     *    - Ensures that the business rule for missed visits is respected:
     *      they can only be rescheduled for today or a future date.
     *
     * 2. Pending or other non-missed visits:
     *    - Existing visit is updated with status DATE_CHANGED and reason from DTO.
     *    - A new scheduled visit is created with the new date.
     *
     * Both types of visits:
     * - The new visit replicates the original visit's relevant fields.
     * - Assigns status SCHEDULED.
     * - Links the new visit to the original visit via parent_id.
     * - Sets creator and branch from the DTO.
     */
    private function rescheduleExistingVisit(EmployeeVisit $visit, ChangeVisitDateDto $dto): EmployeeVisit
    {

        if ($visit->isMissed()) {
            return $this->createNewScheduledVisit($visit, $dto);
        }

        $this->updateVisitForReschedule($visit, $dto);

        event(new VisitDateChanged($visit, $dto->currentDate, $dto->newDate, $dto->reason?->label()));

        return $this->createNewScheduledVisit($visit, $dto);
    }

    private function createNewScheduledVisit(EmployeeVisit $visit, ChangeVisitDateDto $dto): EmployeeVisit
    {
        $newVisit = $visit->replicate(['status', 'scheduled_at', 'reason']);

        $newVisitStatus = Carbon::parse($dto->newDate)->isToday()
            ? EmployeeVisitStatusEnum::PENDING
            : EmployeeVisitStatusEnum::SCHEDULED;

        $newVisit->assignStatus($newVisitStatus);
        $newVisit->assignScheduledAt($dto->newDate);
        $newVisit->assignParent($visit);
        $newVisit->assignCreator($dto->user->id);
        $newVisit->assignBranch($dto->currentBranch->id);
        $newVisit->save();

        return $newVisit;
    }

    private function updateVisitForReschedule(EmployeeVisit $visit, ChangeVisitDateDto $dto): void
    {
        DB::transaction(function () use ($visit, $dto): void {
            $visit->update([
                'reason' => $dto->reason,
                'status' => EmployeeVisitStatusEnum::DATE_CHANGED,
            ]);

            $this->employeeVisitCommentService->addComment(
                $visit,
                __(
                    'Visit rescheduled due to :reason, Additional notes: :notes',
                    ['notes' => $dto->notes, 'reason' => $dto->reason->label()]
                )
            );
        });
    }

    public function createVisit(EmployeeVisitDto $dto): EmployeeVisit
    {
        $employee   = Employee::findOrFail($dto->employeeId);
        $targetUser = $employee->user;

        $currentUser = auth()->user();

        if (! $currentUser->canActOn($targetUser)) {
            abort(403, 'You are not authorized to perform this action.');
        }

        if ($dto->isAnonymous()) {
            $anonymousCustomerData = [
                'name'         => $dto->anonymousCustomer->name,
                'email'        => $dto->anonymousCustomer->email,
                'phone'        => $dto->anonymousCustomer->phone,
                'address'      => $dto->anonymousCustomer->address,
                'city'         => $dto->anonymousCustomer->city,
                'state'        => $dto->anonymousCustomer->state,
                'pin_location' => $dto->anonymousCustomer->pin_location,
                'block_number' => $dto->anonymousCustomer->block_number,
                'road_street'  => $dto->anonymousCustomer->road_street ?? null,
                'building_no'  => $dto->anonymousCustomer->building_no ?? null,
                'cr_number'    => $dto->anonymousCustomer->cr_number,
                'vat_number'   => $dto->anonymousCustomer->vat_number,
            ];

            $anonymousCustomerDto = AnonymousCustomerDto::fromArray(data: $anonymousCustomerData, withAddress: true);
            // Create or get anonymous customer branch
            $anonymousBranch = $this->anonymousCustomerService->create($anonymousCustomerDto);
            $visitableId     = $anonymousBranch->branch->id;
            $visitableType   = AnonymousCustomerBranch::class;
        } else {
            $this->scheduleVisitService->ensureCustomerBelongsToBranches($employee, $dto->customerId);
            $visitableId   = $dto->customerId;
            $visitableType = Branch::class;
        }

        $data = [
            'employee_id'    => $dto->employeeId,
            'visitable_id'   => $visitableId,
            'visitable_type' => $visitableType,
            'scheduled_at'   => $dto->scheduledAt,
            'status'         => $dto->status,
            'branch_id'      => currentBranch()->id,
            'custom_weight'  => $dto->customWeight,
            'weight'         => $dto->weight,
            'purpose'        => $dto->purpose,
            'source_type'    => $dto->sourceType,
            'created_by'     => auth()->id(),
        ];

        $visit = EmployeeVisit::create($data);
        event(new ManualVisitCreated($visit));

        return $visit;
    }

    public function sendDailyReminders(): void
    {
        EmployeeVisit::query()
            ->with(['employee:id', 'employee.user'])
            ->select(['id', 'employee_id', 'scheduled_at'])
            ->where('source_type', SourceTypeEnum::SCHEDULE)
            ->whereDate('scheduled_at', today())
            ->chunkById(200, function ($visitsChunk): void {
                $users = $visitsChunk->pluck('employee.user')
                    ->filter()
                    ->unique('id')
                    ->values();

                Notification::send($users, new DailyVisitReminderNotification);
            });
    }

    public function openFutureVisit(
        int $schedule_id,
        string $start,
        string $end
    ): EmployeeVisitShowResource {
        $visits = $this->visitProjectionService->getProjectedVisitsForSchedule($schedule_id, $start, $end);

        return new EmployeeVisitShowResource($visits?->first());
    }

    public function VisitToShow(
        $visit_id,
        Employee $employee,
        Branch $branch
    ): ?EmployeeVisitShowResource {
        $visit = EmployeeVisit::where('id', $visit_id)
            ->where('branch_id', $branch->id)
            ->with([
                'employee.user',
                'visitable' => function ($morph): void {
                    $morph->morphWith([
                        Branch::class                  => ['addresses'],
                        AnonymousCustomerBranch::class => ['customer'],
                    ]);
                },
                'schedule',
                'comments.employee.user',
                'lines' => function ($q): void {
                    $q->with('orderLine', function ($lineQuery): void {
                        $lineQuery
                            ->select([
                                'id',
                                'order_id',
                                'product_id',
                                'quantity',
                                'currency',
                                'packaging',
                                'price',
                                'total',
                                'status',
                                'variant_id',
                            ])
                            ->with(['variant:id,name,image', 'order:id,uuid']);
                    });
                },
            ])->first();

        $isOwner              = $employee->id === $visit->employee_id;
        $isSharedWithEmployee = $visit->sharedWithEmployees()->where('employees.id', $employee->id)->exists();
        $isManagerOfOwner     = $employee->myEmployees($branch)->where('id', $visit->employee_id)->exists();
        $canViewAll           = $employee->user->hasPermission('view all employee visits');

        if (! $visit || (! $isOwner && ! $isSharedWithEmployee && ! $isManagerOfOwner && ! $canViewAll)) {
            return null;
        }

        return new EmployeeVisitShowResource($visit);
    }

    public function markMissedVisits(): void
    {
        $highbaseBranchId = Branch::getHighbaseBranchId();

        // Other branches: mark as missed
        EmployeeVisit::query()
            ->where('status', EmployeeVisitStatusEnum::PENDING)
            ->whereDate('scheduled_at', '<=', now()->toDateString())
            ->when($highbaseBranchId, fn ($q) => $q->where('branch_id', '!=', $highbaseBranchId))
            ->update(['status' => EmployeeVisitStatusEnum::MISSED]);

        // Highbase branch: reschedule pending visits (scheduled_at <= today) to next day instead of marking missed
        $this->rescheduleHighbasePendingVisitsToNextDay();
    }

    /**
     * Reschedule Highbase pending visits with scheduled_at <= today to the next calendar day.
     * Keeps them Pending so they appear on the next day's list instead of being marked missed.
     */
    private function rescheduleHighbasePendingVisitsToNextDay(): void
    {
        $highbaseBranchId = Branch::getHighbaseBranchId();

        if (! $highbaseBranchId) {
            return;
        }

        $nextDay = Carbon::today()->addDay();

        EmployeeVisit::query()
            ->where('branch_id', $highbaseBranchId)
            ->where('status', EmployeeVisitStatusEnum::PENDING)
            ->whereDate('scheduled_at', '<=', Carbon::today())
            ->update(['scheduled_at' => $nextDay]);
    }

    public function notifyBranchOwnersAboutMissedVisits(): void
    {
        EmployeeVisit::query()
            ->with([
                'branch.account.employees.user',
                'employee.user',
                'visitable',
            ])
            ->where('status', EmployeeVisitStatusEnum::MISSED)
            ->where('scheduled_at', today()->toDateString())
            ->chunkById(200, function ($visitsChunk): void {
                foreach ($visitsChunk as $visit) {
                    $accountUsers = $visit->branch?->account?->getManagementUsers(
                        $visit->branch,
                        ['view all employee visits']
                    );

                    if ($accountUsers->isNotEmpty()) {
                        Notification::send($accountUsers, new MissedVisitNotification(visit: $visit));
                    }
                }
            });
    }

    public function resolveForCustomer(
        Branch|AnonymousCustomerBranch $customerBranch,
        Employee $employee,
        Branch $branch
    ): ?EmployeeVisit {
        return EmployeeVisit::query()
            ->where('visitable_type', $customerBranch::class)
            ->where('visitable_id', $customerBranch->id)
            ->where('branch_id', $branch->id)
            ->where('employee_id', $employee->id)
            ->confirmed()
            ->notInvoiced()
            ->whereDate('confirmed_at', today())
            ->latest('confirmed_at')
            ->first();
    }

    public function buildPayloadForReschedule(EmployeeVisit $visit): array
    {
        return [
            'open'    => true,
            'prefill' => [
                'employee_id' => $visit->employee_id,
                'client'      => $visit->visitable_type === Branch::class
                    ? 'existing'
                    : 'anonymous',

                'customer_id' => $visit->visitable_type === Branch::class
                    ? $visit->visitable_id
                    : null,

                'anonymous_customer' => $visit->visitable_type === AnonymousCustomerBranch::class
                    ? $this->anonymousCustomerPayload($visit)
                    : null,
                'purpose'       => $visit->purpose,
                'custom_weight' => $visit->custom_weight,
            ],
        ];
    }

    private function anonymousCustomerPayload(EmployeeVisit $visit): array
    {
        $branch   = $visit->visitable;
        $customer = $branch->customer;

        return [
            'name'         => $branch->name,
            'email'        => $branch->email,
            'phone'        => $branch->phone,
            'cr_number'    => $customer->cr_number,
            'vat_number'   => $customer->vat_number,
            'pin_location' => $branch->address['pin_location'],
            'address'      => $branch->address['address'],
            'block_number' => $branch->address['block_number'],
            'state'        => $branch->address['state'],
            'city'         => $branch->address['city'],
        ];
    }

    public function shareWithEmployees(EmployeeVisit $visit, array $employeeIds): void
    {
        $employeeIds = collect($employeeIds)
            ->reject(fn ($id): bool => $id === $visit->employee_id)
            ->values()
            ->all();

        $visit->sharedWithEmployees()->syncWithoutDetaching($employeeIds);
    }

    public function revokeSharing(
        EmployeeVisit $visit,
        Employee $employee,
    ): void {
        if (! $visit->sharedWithEmployees()
            ->where('employees.id', $employee->id)
            ->exists()) {
            return;
        }

        $visit->sharedWithEmployees()->detach($employee->id);
    }
}
