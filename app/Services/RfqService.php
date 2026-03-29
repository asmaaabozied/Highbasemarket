<?php

namespace App\Services;

use App\Events\RFQPostedEvent;
use App\Models\RfqPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class RfqService
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function createRfq(array $data): void
    {
        $published_at = $data['published'] ? now() : null;

        $post = RfqPost::query()->create([
            ...Arr::except($data, ['attachment']),
            'published_at' => $published_at,
        ]);

        try {
            $file = Storage::files('chunked_attachments/'.request('attachment'));

            if (request('attachment') && count($file) > 0) {
                $post->addMedia(Storage::path($file[0]))->toMediaCollection('attachments');
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        if ($published_at instanceof \Illuminate\Support\Carbon) {
            RFQPostedEvent::dispatch(auth()->user(), $post);
        }
    }

    public function updateRfq($rfq, array $data): void
    {
        $rfq->update([
            ...Arr::except($data, ['attachment']),
            'published_at' => $data['published'] ? now() : null,
        ]);

        try {
            $file = Storage::files('chunked_attachments/'.request('attachment'));

            if (request('attachment') && count($file) > 0) {
                $rfq->clearMediaCollection('attachments');
                $rfq->addMedia(Storage::path($file[0]))->toMediaCollection('attachments');
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getRfqs(): LengthAwarePaginator
    {
        return RfqPost::query()
            ->where('branch_id', currentBranch()?->id)
            ->with(['owner:id,slug,name', 'category:id,slug,name', 'group:id,name,category_id'])->paginate();
    }
}
