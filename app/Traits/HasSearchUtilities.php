<?php

namespace App\Traits;

trait HasSearchUtilities
{
    /**
     * Return an empty response structure for search.
     */
    protected function emptyResponse(): array
    {
        return [
            'data'  => [],
            'links' => [],
            'meta'  => ['total' => 0],
        ];
    }

    /**
     * Standardize the search pagination response.
     */
    protected function searchResponse($paginatedData): array
    {
        return [
            'data' => $paginatedData->items(),
            'meta' => [
                'has_more' => $paginatedData->hasMorePages(),
                'page'     => $paginatedData->currentPage(),
                'total'    => $paginatedData->total(),
            ],
        ];
    }
}
