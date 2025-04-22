<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaData extends Model
{
    use HasFactory;

    /**
     * Disable the primary key
     */
    public $incrementing = false;
    protected $primaryKey = null;

    /**
     * Disable the timestamps
     */
    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (is_array($item->value)) {
                $item->value = json_encode($item->value);
            }
        });
    }

    function getValueAttribute($value)
    {
        return filterValue($value);
    }

    public function metaable()
    {
        return $this->morphTo();
    }
}
