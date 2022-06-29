<?php

namespace App\Models;

use App\Builders\Builder;
use App\Relations\HasRelated;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Model extends BaseModel
{
    /**
     * Create a new Eloquent builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->toArray();
    }

    /**
     * Define a has-related relationship.
     *
     * @param  string  $related
     * @param  string  $through
     * @param  string|null  $firstKey
     * @param  string|null  $secondKey
     * @param  string|null  $localKey
     * @param  string|null  $secondLocalKey
     * @return \App\Relations\HasRelated
     */
    public function hasRelated($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null): HasRelated
    {
        $through = new $through;

        $firstKey = $firstKey ?: $this->getForeignKey();

        $secondKey = $secondKey ?: $through->getForeignKey();

        return $this->newHasRelated(
            $this->newRelatedInstance($related)->newQuery(),
            $this,
            $through,
            $firstKey,
            $secondKey,
            $localKey ?: $this->getKeyName(),
            $secondLocalKey ?: $through->getKeyName()
        );
    }

    /**
     * Instantiate a new HasRelated relationship.
     *
     * @param  \App\Builders\Builder  $query
     * @param  \App\Models\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Relations\Pivot  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return \App\Relations\HasRelated
     */
    protected function newHasRelated(Builder $query, Model $farParent, Pivot $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey): HasRelated
    {
        return new HasRelated($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }
}
