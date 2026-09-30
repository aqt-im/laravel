<?php

namespace AqtIm\Laravel\Tests\Feature;

use AqtIm\Laravel\Tests\Fixtures\Ticket;
use AqtIm\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\DB;
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

        Queue::assertPushed(CallWebhookJob::class, fn (CallWebhookJob $job) => $job->webhookUrl === 'https://webhook.aqt.im/callback/tickets'
            && isset($job->headers['Signature']));
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

    public function test_the_ticket_url_is_built_from_the_pnr_code(): void
    {
        config(['aqtim.ticket.url' => 'https://ticket.aqt.im/']);

        $this->assertSame('https://ticket.aqt.im/ABC123', aqtim()->ticketUrl('ABC123'));
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
