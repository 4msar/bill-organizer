<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    /**
     * Get slug options
     */
    public function getSlugOptions(): array
    {
        return [
            'source' => 'title',
            'destination' => 'slug',
            'unique' => true,
            'maxLength' => 255,
        ];
    }

    /**
     * Boot the trait
     */
    public static function bootSluggable(): void
    {
        self::creating(function ($model) {
            $model->fillSlug();
        });
    }

    public function fillSlug(): void
    {
        $options = $this->getSlugOptions();
        if (! isset($options['source']) || ! isset($options['destination'])) {
            return;
        }

        $source = $options['source'];
        $destination = $options['destination'];

        if (! empty($this->$destination)) {
            if ($options['unique']) {
                $this->$destination = $this->checkUniqueness(
                    $destination,
                    $this->$destination
                );
            }

            if (isset($options['maxLength'])) {
                $this->$destination = substr($this->$destination, 0, $options['maxLength']);
            }

            return;
        }

        $slug = Str::slug($this->$source);
        if ($options['unique']) {
            $slug = $this->checkUniqueness($destination, $slug);
        }

        if (isset($options['maxLength'])) {
            $slug = substr($slug, 0, $options['maxLength']);
        }

        $this->$destination = $slug;
    }

    /**
     * Ensure the slug is unique within the destination field.
     */
    protected function checkUniqueness(string $destination, string $slug): string
    {
        $counter = 1;
        $originalSlug = $slug;

        while (self::where($destination, $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++.Str::random(6);
        }

        return $slug;
    }
}
