<?php

namespace App\Traits;

use App\Models\MetaData;
use Illuminate\Support\Arr;

trait HasMetaData
{
    /**
     * Initialize the meta relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function meta()
    {
        return $this->morphMany(MetaData::class, 'metaable');
    }

    /**
     * Get the meta value by key
     *
     * @param string $key
     * @param string|mixed $fallback
     * @param boolean $toArray if json
     * @return string|mixed
     */
    public function getMeta($key, $fallback = null, $refresh = false)
    {
        $meta = ($refresh ? $this->meta() : $this->meta)
            ->where('name', $key)->first();

        return $meta->value ?? $fallback;
    }

    /**
     * Check the meta is available or not
     *
     * @param string $key
     * @return boolean
     */
    public function hasMeta($name)
    {
        return $this->meta->hasAny($name);
    }

    /**
     * Remove meta from meta table
     *
     * @param string|array $name
     * @return boolean
     */
    public function removeMeta($name)
    {
        return $this->meta()->whereIn('name', Arr::wrap($name))->delete();
    }

    /**
     * Set the meta value by key
     *
     * @param string|array $name
     * @param string|mixed $value
     * @return string|mixed
     */
    public function setMeta($name, $value = null)
    {
        if (!$this->meta()->where('name', $name)->exists()) {
            $this->meta()->create([
                'name' => $name,
                'value' => $value
            ]);
        } else {
            $this->meta()->where('name', $name)->update(['value' => $value]);
        }

        return $value;
    }

    /**
     * Get the all meta by their key => value
     *
     * @return array|mixed
     */
    public function getSerializedMeta()
    {
        return $this->meta->pluck('value', 'name');
    }
}
