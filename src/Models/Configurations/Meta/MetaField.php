<?php

namespace TronderData\TdCostCalcultaror\Models\Configurations\Meta;

use Illuminate\Database\Eloquent\Model;

class MetaField extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'meta_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'label',
        'description',
        'type',
        'rules',
        'default_value',
        'options',
        'module',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default_value' => 'json',
        'options' => 'json',
    ];

    /**
     * Get all meta data values for this field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metaData()
    {
        return $this->hasMany(MetaData::class, 'key', 'key')
            ->where('module', $this->module);
    }
}
