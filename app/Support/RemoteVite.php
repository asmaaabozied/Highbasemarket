<?php

namespace App\Support;

use Illuminate\Foundation\Vite;

class RemoteVite extends Vite
{
    /**
     * Override the manifest path to load from a remote URL.
     */
    protected function manifestPath($buildDirectory): string
    {
        // Example: Load from a CDN or external server
        return loadViteAsset();
    }

    /**
     * Override to fetch remote manifest content.
     */
    protected function manifest($buildDirectory)
    {
        static $manifest;

        if (! $manifest) {
            $url  = $this->manifestPath($buildDirectory);
            $json = @file_get_contents($url);

            if ($json === false) {
                throw new \RuntimeException("Unable to load Vite manifest from {$url}");
            }

            $manifest = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Invalid JSON in Vite manifest from {$url}");
            }
        }

        return $manifest;
    }
}
