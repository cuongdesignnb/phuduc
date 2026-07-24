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
        if (preg_match('/[\x00-\x1F\x7F]/', $url) || preg_match('/^(?:javascript|data|vbscript|file):/i', $url) || str_starts_with($url, '//')) {
            throw ValidationException::withMessages(['items' => 'Menu URL is not safe.']);
        }
        if ($url[0] === '#') {
            if (preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $url) !== 1) {
                throw ValidationException::withMessages(['items' => 'Menu anchor is not valid.']);
            }

            return $url;
        }
        if (str_starts_with($url, '/')) {
            return $url;
        }
        if (preg_match('/^https?:\/\//i', $url) === 1 && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        if (preg_match('/^mailto:(.+)$/i', $url, $matches) === 1) {
            $address = explode('?', $matches[1], 2)[0];
            if ($address !== '' && filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return $url;
            }
        }
        if (preg_match('/^tel:\+?[0-9][0-9().\-\s]{5,30}$/i', $url) === 1) {
            return $url;
        }

        throw ValidationException::withMessages(['items' => 'Menu URL is not valid.']);
    }
}
