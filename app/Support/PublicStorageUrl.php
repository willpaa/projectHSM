<?php

namespace App\Support;

class PublicStorageUrl
{
    public static function make(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $encodedPath = collect(explode('/', ltrim($path, '/')))
            ->map(fn (string $segment): string => rawurlencode($segment))
            ->implode('/');

        return rtrim(request()->getBaseUrl(), '/') . '/storage/' . $encodedPath;
    }
}
