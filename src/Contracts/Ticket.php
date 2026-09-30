<?php

namespace AqtIm\Laravel\Contracts;

interface Ticket
{
    public function aqtimPnrCode(): string;

    public function aqtimPayload(): array;
}
