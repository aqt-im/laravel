<?php

namespace AqtIm\Laravel\Tests\Feature;

use AqtIm\Laravel\Jobs\SendTicketEvent;
use AqtIm\Laravel\Tests\Fixtures\Ticket;
use AqtIm\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Spatie\WebhookServer\CallWebhookJob;

class SendTicketEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake([CallWebhookJob::class]);
    }

    public function test_a_created_ticket_is_sent(): void
    {
        $ticket = $this->ticket();

        $this->assertSent('created', fn (array $payload) => $payload['id'] === $ticket->id
            && $payload['data']['pnr_code'] === 'ABC123');
    }

    public function test_the_webhook_is_signed_and_sent_to_the_callback(): void
    {
        $this->ticket();

        Queue::assertPushed(CallWebhookJob::class, fn (CallWebhookJob $job) => $job->webhookUrl === 'https://webhook.test.aqt.im/callback/tickets'
            && isset($job->headers['Signature']));
    }

    public function test_the_local_mode_logs_the_webhook_instead_of_sending_it(): void
    {
        config(['aqtim.mode' => 'local', 'aqtim.webhook.secret' => null]);

        Log::spy();

        $this->ticket();

        Queue::assertNotPushed(CallWebhookJob::class);
        Log::shouldHaveReceived('debug')->withArgs(fn (string $message, array $context) => $context['url'] === 'https://webhook.local.aqt.im/callback/tickets'
            && $context['payload']['type'] === 'created'
            && $context['payload']['data']['pnr_code'] === 'ABC123');
    }

    public function test_an_updated_ticket_carries_its_current_state(): void
    {
        $ticket = $this->ticket();
        $ticket->update(['name' => 'Grace']);

        $this->assertSent('updated', fn (array $payload) => $payload['data']['name'] === 'Grace');
    }

    public function test_an_approval_change_is_sent_as_an_update(): void
    {
        $ticket = $this->ticket();
        $ticket->approve();

        $this->assertSent('updated', fn (array $payload) => $payload['data']['approval_status'] === 'approved');
    }

    public function test_a_deleted_ticket_sends_its_pnr_code(): void
    {
        $ticket = $this->ticket();
        $ticket->delete();

        $this->assertSent('deleted', fn (array $payload) => $payload['data'] === ['id' => $ticket->id, 'pnr_code' => 'ABC123']);
    }

    public function test_a_force_deleted_ticket_is_still_announced(): void
    {
        $ticket = $this->ticket();
        $ticket->forceDelete();

        $this->assertSent('deleted', fn (array $payload) => $payload['data']['pnr_code'] === 'ABC123');
    }

    public function test_a_restored_ticket_is_sent(): void
    {
        $ticket = $this->ticket();
        $ticket->delete();
        $ticket->restore();

        $this->assertSent('restored', fn (array $payload) => $payload['data']['deleted_at'] === null);
    }

    public function test_a_rolled_back_ticket_is_not_sent(): void
    {
        try {
            DB::transaction(function () {
                $this->ticket();

                throw new RuntimeException;
            });
        } catch (RuntimeException) {
        }

        Queue::assertNotPushed(CallWebhookJob::class);
    }

    public function test_a_ticket_is_sent_once_the_transaction_commits(): void
    {
        DB::transaction(fn () => $this->ticket());

        $this->assertSent('created', fn (array $payload) => $payload['data']['pnr_code'] === 'ABC123');
    }

    public function test_the_event_shares_the_webhook_server_queue_by_default(): void
    {
        config(['webhook-server.queue' => 'webhook.server']);

        Queue::fake([SendTicketEvent::class]);

        $this->ticket();

        Queue::assertPushedOn('webhook.server', SendTicketEvent::class);
    }

    public function test_the_event_runs_on_the_configured_queue(): void
    {
        config(['webhook-server.queue' => 'webhook.server', 'aqtim.webhook.queue' => 'aqtim']);

        Queue::fake([SendTicketEvent::class]);

        $this->ticket();

        Queue::assertPushedOn('aqtim', SendTicketEvent::class);
    }

    private function ticket(): Ticket
    {
        return Ticket::create(['pnr_code' => 'ABC123', 'name' => 'Ada']);
    }

    private function assertSent(string $type, callable $matches): void
    {
        Queue::assertPushed(CallWebhookJob::class, fn (CallWebhookJob $job) => $job->payload['type'] === $type
            && $matches($job->payload));
    }
}
