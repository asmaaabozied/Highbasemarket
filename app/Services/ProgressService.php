<?php

namespace App\Services;

use App\Enum\ActionType;
use App\Models\Action;
use App\Models\Progress;
use App\Models\QuoteDetail;
use App\Models\Step;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    public function progressCreateAction(array $data)
    {
        $action = Action::query()->create([
            'name'    => $data['name'],
            'status'  => Action::PENDING,
            'step_id' => $data['step_id'],

        ]);

        if ($data['type'] === ActionType::PAYMENT) {
            $fileAdder = $action->addMultipleMediaFromRequest('bills')
                ->each(function ($fileAdder): void {
                    $fileAdder->toMediaCollection('bills');
                });
        }

        if ($data['type'] === ActionType::DOCUMENTS) {
            $fileAdder = $action->addMultipleMediaFromRequest('documents')
                ->each(function ($fileAdder): void {
                    $fileAdder->toMediaCollection('documents');
                });
        }

        return $action;
    }

    public function getTemplateProgress(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Progress::query()
            ->with('steps')
            ->where('assigned', false)
            ->where('branch_id', currentBranch()?->id)
            ->paginate();
    }

    public function getAssignedProgress(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Progress::query()
            ->with('quoteDetail')
            ->where('assigned', true)
            ->where('branch_id', currentBranch()?->id)
            ->paginate()->withQueryString();
    }

    public function createProgress($data): void
    {
        DB::transaction(function () use ($data): void {
            $model = Progress::query()->create(
                [
                    'title'     => $data['title'],
                    'status'    => Progress::ACTIVE,
                    'branch_id' => currentBranch()?->id,
                ]
            );
            $this->stepProcess($model, $data['steps']);
        });
    }

    public function deleteProgress($id): void
    {
        Progress::query()->findOrFail($id)->delete();
    }

    public function updateProgress($progress, $data): void
    {
        DB::transaction(function () use ($progress, $data): void {
            $progress->update(Arr::except($data, 'steps'));
            $this->stepProcess($progress, $data['steps']);
        });
    }

    public function progressLookup(): \Illuminate\Database\Eloquent\Collection
    {
        return Progress::query()->select('id', 'title')
            ->where('assigned', false)
            ->where('branch_id', currentBranch()?->id)
            ->orderByDesc('id')
            ->get();
    }

    public function stepProcess($progress, $steps): void
    {
        if ($progress->steps->count() > 0) {
            $progress->steps()->delete();
        }

        foreach ($steps as $step) {
            $progress->steps()->updateOrCreate(
                ['id' => $step['id'] ?? null], [...$step, 'status' => Step::INACTIVE]
            );
        }
    }

    public function replicateProgress($progressId, $quoteId = null)
    {
        $template = null;

        if ($quoteId) {
            $quoteDetail = QuoteDetail::query()->findOrFail($quoteId);
            $progress    = $quoteDetail->progress()->where('id', $progressId)->first();
            $template    = Progress::query()->where('title', $progress?->title)->first();
            $quoteDetail->progress()->delete();
        }

        if (! $template) {
            $template = Progress::query()->findOrFail($progressId);
        }

        $newProgress = $template->replicate()->fill([
            'assigned' => true,
        ]);

        $newProgress->save();
        $newProgress->steps()->createMany($template->steps->toArray());

        return $newProgress;

    }
}
