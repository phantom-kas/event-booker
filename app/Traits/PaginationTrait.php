<?php

namespace App\Traits;

trait PaginationTrait
{
    /**
     * Format paginated API response in a consistent structure
     */
    public function paginatedResponse($paginatedData)
    {
        return response()->json([
            'success' => true,

            'data' => $paginatedData->items(),   // The actual results

            'meta' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page'    => $paginatedData->lastPage(),
                'per_page'     => $paginatedData->perPage(),
                'total'        => $paginatedData->total(),
                'from'         => $paginatedData->firstItem(),
                'to'           => $paginatedData->lastItem(),
                'has_more'     => $paginatedData->hasMorePages(),
            ]
        ]);
    }
}
