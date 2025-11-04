<?php

use App\Services\DeferredExecutionService;

if (!function_exists('defer')) {
    /**
     * Simule la fonction defer() de Laravel 12
     *
     * @param string $action
     * @param array $data
     * @param int $delay
     * @param string $queue
     * @return void
     */
    function defer(string $action, array $data = [], int $delay = 0, string $queue = 'default'): void
    {
        app(DeferredExecutionService::class)->defer($action, $data, $delay, $queue);
    }
}

if (!function_exists('deferWhatsApp')) {
    /**
     * Différer une notification WhatsApp
     */
    function deferWhatsApp(array $data): void
    {
        app(DeferredExecutionService::class)->deferWhatsApp($data);
    }
}

if (!function_exists('deferAnalytics')) {
    /**
     * Différer une opération analytics
     */
    function deferAnalytics(array $data): void
    {
        app(DeferredExecutionService::class)->deferAnalytics($data);
    }
}

if (!function_exists('deferEmail')) {
    /**
     * Différer un email
     */
    function deferEmail(array $data): void
    {
        app(DeferredExecutionService::class)->deferEmail($data);
    }
}

if (!function_exists('asset_path')) {
    /**
     * Generate a URL for the application assets.
     *
     * @param  string  $path
     * @return string
     */
    function asset_path($path = '')
    {
        return url(config('app.assets_path_url') . ltrim($path, '/'));
    }
}

if (!function_exists('storage_asset')) {
    /**
     * Generate a URL for storage assets.
     *
     * @param  string  $path
     * @return string
     */
    function storage_asset($path = '')
    {
        return url('storage/' . ltrim($path, '/'));
    }
}
