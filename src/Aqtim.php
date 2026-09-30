<?php

namespace AqtIm\Laravel;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

class Aqtim
{
    public function __construct(
        private readonly Repository $config,
        private readonly string $environment,
    ) {}

    public function mode(): Mode
    {
        $mode = $this->config->get('aqtim.mode');

        if (blank($mode)) {
            return Mode::forEnvironment($this->environment);
        }

        return Mode::tryFrom($mode)
            ?? throw new InvalidArgumentException("Unknown aqt.im mode [{$mode}], expected production, test or local.");
    }

    public function ticketUrl(string $pnrCode): string
    {
        $url = $this->config->get('aqtim.ticket.url') ?? $this->mode()->ticketUrl();

        return rtrim($url, '/').'/'.$pnrCode;
    }

    public function webhookUrl(): string
    {
        $url = $this->config->get('aqtim.webhook.url') ?? $this->mode()->webhookUrl();

        return rtrim($url, '/').'/callback/tickets';
    }

    public function webhookSecret(): string
    {
        return (string) $this->config->get('aqtim.webhook.secret');
    }

    public function queue(): ?string
    {
        return $this->config->get('aqtim.webhook.queue') ?? $this->config->get('webhook-server.queue');
    }
}
