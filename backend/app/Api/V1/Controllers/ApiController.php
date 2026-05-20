<?php

namespace App\Api\V1\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class ApiController
{
    use ApiResponse;

    public const int DEFAULT_PER_PAGE = 20;

    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'path' => $paginator->path(),
        ];
    }
}
