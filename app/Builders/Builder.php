<?php

namespace App\Builders;

use App\Pagination\ApiPaginator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class Builder extends BaseBuilder
{
    /**
     * Paginate the given query.
     *
     * @param  int|null  $perPage
     * @param  string|null  $resultsKey
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return LengthAwarePaginator
     *
     * @throws InvalidArgumentException|BindingResolutionException
     */
    public function paginate($perPage = null, $resultsKey = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $resultsKey = $resultsKey ?: $this->model->getTable();

        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        $results = ($total = $this->toBase()->getCountForPagination())
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
            'resultsKey' => $resultsKey,
        ]);
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param Collection $items
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param array $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     * @throws BindingResolutionException
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Container::getInstance()->makeWith(ApiPaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

}
