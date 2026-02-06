<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface Query
{
    /**
     * Execute the query and return results
     */
    public function get(): Collection|LengthAwarePaginator;
}
