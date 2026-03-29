<?php

namespace App\Services;

use App\Models\QuoteDetail;
use App\Models\Terms;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class TermsService
{
    public function getTemplateTerms(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Terms::query()
            ->where('assigned', false)
            ->where('branch_id', currentBranch()?->id)
            ->paginate();
    }

    public function getAssignedTerms(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Terms::query()
            ->where('assigned', true)
            ->where('branch_id', currentBranch()?->id)
            ->paginate();
    }

    public function termsLookup(): \Illuminate\Database\Eloquent\Collection
    {
        return Terms::query()->select('id', 'title')
            ->where('assigned', false)
            ->where('branch_id', currentBranch()?->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function createTerms(array $data)
    {
        $term = Terms::query()->create([
            ...Arr::except($data, 'file'),
            'branch_id' => currentBranch()?->id,
        ]);

        if (isset($data['file']) && $data['file']) {
            $term->addMediaFromRequest('file')->toMediaCollection('terms');
        }

        return $term;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function updateTerms(Terms $term, array $data): void
    {
        $term->update(Arr::except($data, 'file'));

        if (isset($data['file']) && $data['file']) {
            $term->clearMediaCollection('terms');
            $term->addMediaFromRequest('file')->toMediaCollection('terms');
        }
    }

    public function deleteTerms(Terms $term): void
    {
        $term->delete();
    }

    /**
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function replicateTerms($termsId, $quoteId = null)
    {

        if ($quoteId) {
            $quoteDetail = QuoteDetail::query()->findOrFail($quoteId);
            $quoteDetail->progress()->delete();
        }

        $term = Terms::query()->findOrFail($termsId);

        $newTerm = $term->replicate()->fill([
            'assigned' => true,
        ]);
        $newTerm->save();

        try {
            if ($term->attachment && Storage::exists(ltrim(parse_url((string) $term->attachment, PHP_URL_PATH), '/storage/'))) {
                $newTerm->addMediaFromUrl($term->attachment)->toMediaCollection('terms');
            }
        } catch (\Exception) {
            // Handle the exception if needed, e.g., log it
        }

        return $newTerm;

    }

    public function getTermsTemplateById($id)
    {
        return Terms::query()
            ->where('id', $id)
            ->where('assigned', false)
            ->where('branch_id', currentBranch()?->id)
            ->first();
    }

    public function getDefaultTerm($branchId = null)
    {
        if (! $branchId) {
            $branchId = currentBranch()?->id;
        }

        return Terms::query()
            ->where('assigned', false)
            ->where('branch_id', $branchId)
            ->where('term_default', true)
            ->first();
    }

    public function updateDefaultTerm($id, $status): bool
    {
        return $this->getTermsTemplateById($id)->update(['term_default' => $status]);
    }
}
