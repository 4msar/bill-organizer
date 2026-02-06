<?php

namespace App\Contracts;

interface Action
{
    /**
     * Execute the action
     */
    public function execute(mixed ...$params);
}
