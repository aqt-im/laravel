<?php

namespace AqtIm\Laravel\Jobs;

use AqtIm\Laravel\Contracts\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\WebhookServer\WebhookCall;

class SendTicketEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $model,
        public readonly int|string $key,
        public readonly string $type,
        public readonly ?array $data = null,
    ) {
        $this->onQueue(aqtim()->queue());
        $this->afterCommit();
    }

    public static function for(Model&Ticket $model, string $type): void
    {
        static::dispatch($model::class, $model->getKey(), $type);
    }

    public static function deleted(Model&Ticket $model): void
    {
        static::dispatch($model::class, $model->getKey(), 'deleted', [
            'id' => $model->getKey(),
            'pnr_code' => $model->aqtimPnrCode(),
        ]);
    }

    public function handle(): void
    {
        $data = $this->data ?? $this->model::withoutGlobalScopes()->find($this->key)?->aqtimPayload();

        if ($data === null) {
            return;
        }

        WebhookCall::create()
            ->url(aqtim()->webhookUrl())
            ->payload([
                'id' => $this->key,
                'type' => $this->type,
                'timestamp' => now()->toIso8601String(),
                'data' => $data,
            ])
            ->useSecret(aqtim()->webhookSecret())
            ->dispatch();
    }
}
