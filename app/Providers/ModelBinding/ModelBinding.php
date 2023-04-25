<?php

namespace App\Providers\ModelBinding;

interface ModelBinding
{
    /**
     * Binding route model
     */
    public function bindModel(): void;

    /**
     * Register route pattern so we can filter what kind of pattern that
     * will be accepted by the route model binding.
     */
    public function registerPattern(): void;
}
