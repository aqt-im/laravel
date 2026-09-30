<?php

namespace AqtIm\Laravel\Concerns;

use AqtIm\Laravel\Contracts\Ticket;
use AqtIm\Laravel\Jobs\SendTicketEvent;

trait SendsTicketEvents
{
    public static function bootSendsTicketEvents(): void
    {
        static::created(fn (Ticket $model) => SendTicketEvent::for($model, 'created'));
        static::updated(fn (Ticket $model) => SendTicketEvent::for($model, 'updated'));
        static::deleted(fn (Ticket $model) => SendTicketEvent::deleted($model));

        static::registerModelEvent('restored', fn (Ticket $model) => SendTicketEvent::for($model, 'restored'));
        static::registerModelEvent('approvalChanged', fn (Ticket $model) => SendTicketEvent::for($model, 'updated'));
    }
}
