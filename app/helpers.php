<?php

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