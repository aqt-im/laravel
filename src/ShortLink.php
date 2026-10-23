<?php

namespace AqtIm\Laravel;

class ShortLink
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly bool $qr = false,
    ) {}

    public function user(string $username): string
    {
        return $this->to('u', $username);
    }

    public function artist(string $slug): string
    {
        return $this->to('a', $slug);
    }

    public function organizer(string $slug): string
    {
        return $this->to('o', $slug);
    }

    public function event(string $slug): string
    {
        return $this->to('e', $slug);
    }

    public function venue(string $slug): string
    {
        return $this->to('v', $slug);
    }

    private function to(string $prefix, string $slug): string
    {
        $url = rtrim($this->baseUrl, '/')."/{$prefix}/".rawurlencode($slug);

        return $this->qr ? "{$url}?qr" : $url;
    }
}
