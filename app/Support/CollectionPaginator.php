<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CollectionPaginator
{
    public static function paginate(Collection $items, int $perPage = 15): LengthAwarePaginator
    {
        $page = request('page', 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->all()]
        );
    }
}
