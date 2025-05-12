<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class MetaData extends Model
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

        self::creating(function ($item) {
            if (is_array($item->value)) {
                $item->value = json_encode($item->value);
            }
        });
    }

    public function getValueAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        // Check if value is boolean
        $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($boolean !== null) {
            return $boolean;
        }

        // Check if value is valid JSON
        if (is_string($value) && json_validate($value)) {
            $decoded = json_decode($value, true);

            return array_map(fn ($item) => is_string($item) ? trim($item) : $item, $decoded);
        }

        return $value;
    }

    public function metaable()
    {
        return $this->morphTo();
    }
}
