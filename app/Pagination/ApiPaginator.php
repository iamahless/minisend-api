<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class ApiPaginator extends LengthAwarePaginator
{
    /**
     * The results key.
     *
     * @var string
     */
    protected $resultsKey;

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            $this->resultsKey => $this->items->toArray(),
            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total_items' => $this->total(),
            ],
        ];
    }
}
