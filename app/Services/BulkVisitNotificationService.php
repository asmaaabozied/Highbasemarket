<?php

namespace App\Services;

use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\EmployeeVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BulkVisitNotificationService
{
    private const VISITABLE_TYPE_ANONYMOUS = 'anonymous';

    private const VISITABLE_TYPE_BRANCH = 'branch';

    private const VISITABLE_TYPE_BOTH = 'both';

    public function getEligibleVisitsQuery(array $filters = []): Builder
    {
        $baseQuery      = $this->buildBaseQuery($filters);
        $uniqueVisitIds = $this->getUniqueVisitIds($filters);

        return $baseQuery->whereIn('employee_visits.id', $uniqueVisitIds)
            ->orderBy('employee_visits.confirmed_at', 'desc');
    }

    public function getEligibleVisitsCount(array $filters = []): int
    {
        return $this->buildUniqueVisitsSubquery($filters)->count();
    }

    public function getPreview(array $filters = [], int $limit = 100): array
    {
        $query  = $this->getEligibleVisitsQuery($filters)->limit($limit);
        $visits = $query->get();

        return $visits->map(fn (EmployeeVisit $visit): array => $this->mapVisitToPreview($visit))->toArray();
    }

    protected function buildBaseQuery(array $filters): Builder
    {
        $query = EmployeeVisit::query()
            ->whereNotNull('confirmed_at')
            ->with($this->getEagerLoadRelations());

        $this->applyAllFiltersToQuery($query, $filters);

        return $query;
    }

    protected function getUniqueVisitIds(array $filters): Collection
    {
        return $this->buildUniqueVisitsSubquery($filters)->pluck('latest_visit_id');
    }

    protected function buildUniqueVisitsSubquery(array $filters): Builder
    {
        $subquery = EmployeeVisit::query()
            ->selectRaw('MAX(id) as latest_visit_id')
            ->whereNotNull('confirmed_at')
            ->groupBy('visitable_id', 'visitable_type');

        $this->applyAllFiltersToQuery($subquery, $filters);

        return $subquery;
    }

    protected function applyAllFiltersToQuery(Builder $query, array $filters): void
    {
        $this->applyVisitableTypeFilter($query, $filters);
        $this->applyBranchIdFilters($query, $filters);
        $this->applyCustomerIdFilters($query, $filters);
        $this->applyNameFilters($query, $filters);
        $this->applyEmailFilters($query, $filters);
        $this->applyDateRangeFilters($query, $filters);
    }

    protected function applyVisitableTypeFilter(Builder $query, array $filters): void
    {
        if (! isset($filters['visitable_type'])) {
            return;
        }

        match ($filters['visitable_type']) {
            self::VISITABLE_TYPE_ANONYMOUS => $query->where('visitable_type', AnonymousCustomerBranch::class),
            self::VISITABLE_TYPE_BRANCH    => $query->where('visitable_type', Branch::class),
            self::VISITABLE_TYPE_BOTH      => null,
            default                        => null,
        };
    }

    protected function applyBranchIdFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['branch_ids'])) {
            $query->whereIn('branch_id', $this->parseIds($filters['branch_ids']));
        }

        if (! empty($filters['skip_branch_ids'])) {
            $query->whereNotIn('branch_id', $this->parseIds($filters['skip_branch_ids']));
        }
    }

    protected function applyCustomerIdFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['customer_ids'])) {
            $query->whereIn('visitable_id', $this->parseIds($filters['customer_ids']));
        }

        if (! empty($filters['skip_customer_ids'])) {
            $query->whereNotIn('visitable_id', $this->parseIds($filters['skip_customer_ids']));
        }
    }

    protected function applyNameFilters(Builder $query, array $filters): void
    {
        if (empty($filters['names'])) {
            return;
        }

        $names = $this->normalizeFilterArray($filters['names']);

        $query->where(function ($q) use ($names): void {
            foreach ($names as $name) {
                $q->orWhere(function (\Illuminate\Database\Eloquent\Builder $subQ) use ($name): void {
                    $this->addNameFilterForBranch($subQ, $name);
                    $this->addNameFilterForAnonymous($subQ, $name);
                });
            }
        });
    }

    protected function applyEmailFilters(Builder $query, array $filters): void
    {
        if (empty($filters['emails'])) {
            return;
        }

        $emails = $this->normalizeFilterArray($filters['emails']);

        $query->where(function ($q) use ($emails): void {
            foreach ($emails as $email) {
                $q->orWhere(function (\Illuminate\Database\Eloquent\Builder $subQ) use ($email): void {
                    $this->addEmailFilterForBranch($subQ, $email);
                    $this->addEmailFilterForAnonymous($subQ, $email);
                });
            }
        });
    }

    protected function addNameFilterForBranch(Builder $query, string $name): void
    {
        $query->orWhere(function ($subQ) use ($name): void {
            $subQ->where('visitable_type', Branch::class)
                ->whereHas('visitable', fn ($q) => $q->where('name', 'like', "%{$name}%"));
        });
    }

    protected function addNameFilterForAnonymous(Builder $query, string $name): void
    {
        $query->orWhere(function ($subQ) use ($name): void {
            $subQ->where('visitable_type', AnonymousCustomerBranch::class)
                ->whereHas('visitable', fn ($q) => $q->where('name', 'like', "%{$name}%"));
        });
    }

    protected function addEmailFilterForBranch(Builder $query, string $email): void
    {
        $query->orWhere(function ($subQ) use ($email): void {
            $subQ->where('visitable_type', Branch::class)
                ->whereHas('visitable', fn ($q) => $q->where('email', 'like', "%{$email}%"));
        });
    }

    protected function addEmailFilterForAnonymous(Builder $query, string $email): void
    {
        $query->orWhere(function ($subQ) use ($email): void {
            $subQ->where('visitable_type', AnonymousCustomerBranch::class)
                ->whereHas('visitable', fn ($q) => $q->where('email', 'like', "%{$email}%"));
        });
    }

    protected function applyDateRangeFilters(Builder $query, array $filters): void
    {
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('confirmed_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('confirmed_at', '<=', $filters['date_to']);
        }
    }

    protected function getEagerLoadRelations(): array
    {
        return [
            'visitable' => function ($morphTo): void {
                $morphTo->morphWith([
                    Branch::class                  => ['account'],
                    AnonymousCustomerBranch::class => ['customer'],
                ]);
            },
            'branch',
            'employee.account',
        ];
    }

    protected function mapVisitToPreview(EmployeeVisit $visit): array
    {
        $visitable = $visit->visitable;

        return [
            'visit_id'        => $visit->id,
            'visitable_type'  => $visit->visitable_type,
            'visitable_id'    => $visit->visitable_id,
            'visitable_name'  => $visitable?->name ?? 'N/A',
            'visitable_email' => $visitable?->email ?? 'N/A',
            'branch_id'       => $visit->branch_id,
            'branch_name'     => $visit->branch?->name ?? 'N/A',
            'confirmed_at'    => $visit->confirmed_at?->toDateTimeString(),
        ];
    }

    protected function parseIds(string|array $ids): array
    {
        if (is_array($ids)) {
            return array_map(intval(...), $ids);
        }

        return array_map(intval(...), array_filter(explode(',', $ids)));
    }

    protected function normalizeFilterArray(string|array $value): array
    {
        if (is_array($value)) {
            return array_map(trim(...), $value);
        }

        return array_map(trim(...), explode(',', $value));
    }
}
