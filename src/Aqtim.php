<?php

namespace AqtIm\Laravel;

use Illuminate\Contracts\Config\Repository;

class Aqtim
{
    public function __construct(
        private readonly Repository $config,
    ) {}

    public function ticketUrl(string $pnrCode): string
    {
        return rtrim($this->config->get('aqtim.ticket.url'), '/').'/'.$pnrCode;
    }

    public function webhookUrl(): string
    {
        return rtrim($this->config->get('aqtim.webhook.url'), '/').'/callback/tickets';
    }

    public function webhookSecret(): string
    {
        return (string) $this->config->get('aqtim.webhook.secret');
    }

    public function queue(): ?string
    {
        return $this->config->get('aqtim.webhook.queue');
    }
}
