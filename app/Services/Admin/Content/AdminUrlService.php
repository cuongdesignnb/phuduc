<?php

namespace App\Services\Admin\Content;

use Illuminate\Validation\ValidationException;

class AdminUrlService
{
    public function normalize(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $url) || preg_match('/^(?:javascript|data|vbscript|file):/i', $url)) {
            throw ValidationException::withMessages(['items' => 'Menu URL is not safe.']);
        }
        if ($url[0] === '#') {
            if (preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $url) !== 1) {
                throw ValidationException::withMessages(['items' => 'Menu anchor is not valid.']);
            }

            return $url;
        }
        if (str_starts_with($url, '/')) {
            if (str_starts_with($url, '//')) {
                throw ValidationException::withMessages(['items' => 'Protocol-relative URLs are not allowed.']);
            }

            return $url;
        }
        if (preg_match('/^(?:https?|mailto|tel):/i', $url) === 1 && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        throw ValidationException::withMessages(['items' => 'Menu URL is not valid.']);
    }
}
