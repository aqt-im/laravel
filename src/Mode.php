<?php

namespace AqtIm\Laravel;

enum Mode: string
{
    case Production = 'production';
    case Test = 'test';
    case Local = 'local';

    public static function forEnvironment(string $environment): self
    {
        return match ($environment) {
            'production' => self::Production,
            'test' => self::Test,
            default => self::Local,
        };
    }

    public function webhookUrl(): string
    {
        return match ($this) {
            self::Production => 'https://webhook.aqt.im',
            self::Test => 'https://webhook.test.aqt.im',
            self::Local => 'https://webhook.local.aqt.im',
        };
    }

    public function ticketUrl(): string
    {
        return match ($this) {
            self::Production => 'https://ticket.aqt.im',
            self::Test => 'https://ticket.test.aqt.im',
            self::Local => 'https://ticket.local.aqt.im',
        };
    }

    public function sends(): bool
    {
        return $this !== self::Local;
    }
}
