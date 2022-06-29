<?php

namespace App\Relations;

use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection as BaseCollection;

class HasRelated extends HasManyThrough
{
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints(): void
    {
        $localValue = $this->farParent[$this->localKey];

        $this->performJoin();

        if (static::$constraints) {
            $this->query->where(function ($query) use ($localValue) {
                $query->where($this->getQualifiedFirstKeyName(), '=', $localValue)
                    ->orWhere($this->getQualifiedParentKeyName(), '=', $localValue);
            })->where($this->getQualifiedFarKeyName(), '!=', $localValue);
        }
    }

    /**
     * Set the join clause on the query.
     *
     * @param Builder|null $query
     * @return void
     */
    protected function performJoin(Builder $query = null): void
    {
        $query = $query ?: $this->query;

        $farKey = $this->getQualifiedFarKeyName();

        $query->join($this->throughParent->getTable(), function ($join) use ($farKey) {
            $join->on($this->getQualifiedParentKeyName(), '=', $farKey)
                ->orOn($this->getQualifiedFirstKeyName(), '=', $farKey);
        });

        if ($this->throughParentSoftDeletes()) {
            $query->withGlobalScope('SoftDeletableHasManyThrough', function ($query) {
                $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
            });
        }
    }

    /**
     * Adds the provided Model to the current Relationship.
     *
     * @param \App\Models\Model|string $model
     * @return void
     */
    public function link($model)
    {
        if ($model instanceof Model) {
            $model = $model[$this->secondKey];
        }

        $existingRelationship = $this->throughParent::where(function($query) use ($model) {
            $query->where($this->firstKey, $this->farParent[$this->localKey])
                ->where($this->secondLocalKey, $model);
        })->orWhere(function($query) use ($model) {
            $query->where($this->secondLocalKey, $this->farParent[$this->localKey])
                ->where($this->firstKey, $model);
        })->count();

        if ($existingRelationship === 0) {
            $this->throughParent::create([
                $this->firstKey => $this->farParent[$this->localKey],
                $this->secondLocalKey => $model,
            ]);
        }
    }

    /**
     * Remove the provided Model to the current Relationship.
     *
     * @param string|\App\Models\Model $model
     * @return void
     */
    public function unlink(string|Model $model): void
    {
        if ($model instanceof Model) {
            $model = $model[$this->secondKey];
        }

        $this->throughParent::where(function($query) use ($model) {
            $query->where($this->firstKey, $this->farParent[$this->localKey])
                ->where($this->secondLocalKey, $model);
        })->orWhere(function($query) use ($model) {
            $query->where($this->secondLocalKey, $this->farParent[$this->localKey])
                ->where($this->firstKey, $model);
        })->each(function ($item) {
            $item->delete();
        });
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param BaseCollection|\Illuminate\Database\Eloquent\Model|array  $ids
     * @param bool $unlinking
     */
    public function sync($ids, bool $unlinking = true): void
    {
        // First we need to attach any of the associated models that are not currently
        // in this joining table. We'll spin through the given IDs, checking to see
        // if they exist in the array of current ones, and if not we will insert.

        $existingRelationships_1 = $this->throughParent::where($this->firstKey, $this->farParent[$this->localKey])
            ->pluck($this->secondLocalKey);
        $existingRelationships_2 = $this->throughParent::where($this->secondLocalKey, $this->farParent[$this->localKey])
            ->pluck($this->firstKey);
        $current = $existingRelationships_1->merge($existingRelationships_2)
            ->toArray();

        $detach = array_diff($current, array_keys(
            $records = $this->formatRecordsList($this->parseIds($ids))
        ));

        $attach = array_diff(array_keys($records), $current);

        // Next, we will take the differences of the currents and given IDs and unlink
        // all of the entities that exist in the "current" array but are not in the
        // array of the new IDs given to the method which will complete the sync.
        if ($unlinking && count($detach) > 0) {
            foreach ($detach as $id) {
                $this->unlink($id);
            }
        }

        // Now we are finally ready to link the new records.
        if (count($attach) > 0) {
            foreach ($attach as $id) {
                $this->link($id);
            }
        }
    }

    /**
     * Get all of the IDs from the given mixed value.
     *
     * @param  mixed  $value
     * @return array
     */
    protected function parseIds($value)
    {
        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return [$value->{$this->secondKey}];
        }

        if ($value instanceof Collection) {
            return $value->pluck($this->secondKey)->all();
        }

        if ($value instanceof BaseCollection) {
            return $value->toArray();
        }

        return (array) $value;
    }

    /**
     * Format the sync / toggle record list so that it is keyed by ID.
     *
     * @param  array  $records
     * @return array
     */
    protected function formatRecordsList(array $records): array
    {
        return collect($records)->mapWithKeys(function ($attributes, $id) {
            if (! is_array($attributes)) {
                [$id, $attributes] = [$attributes, []];
            }

            return [$id => $attributes];
        })->all();
    }

}
