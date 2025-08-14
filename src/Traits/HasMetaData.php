<?php

namespace TronderData\TdCostCalcultaror\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMetaData
{
    /**
     * Get all meta data for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function metaData(): MorphMany
    {
        // Using our local MetaData model
        return $this->morphMany(\TronderData\TdCostCalcultaror\Models\Configurations\Meta\MetaData::class, 'parent');
    }

    /**
     * Get a meta value by key.
     *
     * @param string $key The meta key
     * @param mixed $default Default value if not found
     * @param string|null $module Optional module name
     * @return mixed
     */
    public function getMetaValue(string $key, $default = null, ?string $module = null)
    {
        $query = $this->metaData()->where('key', $key);
        
        if ($module) {
            $query->where('module', $module);
        }
        
        $meta = $query->first();
        return $meta ? $meta->value : $default;
    }
    
    /**
     * Get a meta value by key (alias for getMetaValue)
     * Used for backward compatibility.
     *
     * @param string $key The meta key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function getMetaField(string $key, $default = null)
    {
        return $this->getMetaValue($key, $default);
    }

    /**
     * Set a meta value.
     *
     * @param string $key The meta key
     * @param mixed $value The value to store
     * @param string $module Module name for organization
     * @return mixed The created or updated meta record
     */
    public function setMetaValue(string $key, $value, string $module = 'core')
    {
        $meta = $this->metaData()
            ->where('key', $key)
            ->where('module', $module)
            ->first();
        
        if ($meta) {
            $meta->update([
                'value' => $value
            ]);
        } else {
            $meta = $this->metaData()->create([
                'key' => $key,
                'value' => $value,
                'module' => $module
            ]);
        }
        
        return $meta;
    }

    /**
     * Set a meta value (alias for setMetaValue)
     * Used for backward compatibility.
     *
     * @param string $key The meta key
     * @param mixed $value The value to store
     * @param string $module Module name for organization (defaults to 'core')
     * @return mixed The created or updated meta record
     */
    public function setMetaField(string $key, $value, string $module = 'core')
    {
        return $this->setMetaValue($key, $value, $module);
    }
    
    /**
     * Get all meta values as an array.
     *
     * @param string|null $module Optional filter by module
     * @return array
     */
    public function getAllMetaValues(?string $module = null): array
    {
        $query = $this->metaData();
        
        if ($module) {
            $query->where('module', $module);
        }
        
        return $query->get()
            ->mapWithKeys(function ($meta) {
                return [$meta->key => $meta->value];
            })
            ->toArray();
    }

    /**
     * Delete a meta value.
     *
     * @param string $key The meta key
     * @param string|null $module Optional module name
     * @return bool
     */
    public function deleteMetaValue(string $key, ?string $module = null): bool
    {
        $query = $this->metaData()->where('key', $key);
        
        if ($module) {
            $query->where('module', $module);
        }
        
        return (bool) $query->delete();
    }
}
