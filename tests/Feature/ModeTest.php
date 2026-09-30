<?php

namespace AqtIm\Laravel\Tests\Feature;

use AqtIm\Laravel\Aqtim;
use AqtIm\Laravel\Mode;
use AqtIm\Laravel\Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class ModeTest extends TestCase
{
    public static function modes(): array
    {
        return [
            'production' => ['production', 'https://webhook.aqt.im/callback/tickets', 'https://ticket.aqt.im/ABC123'],
            'test' => ['test', 'https://webhook.test.aqt.im/callback/tickets', 'https://ticket.test.aqt.im/ABC123'],
            'local' => ['local', 'https://webhook.local.aqt.im/callback/tickets', 'https://ticket.local.aqt.im/ABC123'],
        ];
    }

    #[DataProvider('modes')]
    public function test_each_mode_has_its_own_addresses(string $mode, string $webhookUrl, string $ticketUrl): void
    {
        config(['aqtim.mode' => $mode]);

        $this->assertSame($webhookUrl, aqtim()->webhookUrl());
        $this->assertSame($ticketUrl, aqtim()->ticketUrl('ABC123'));
    }

    public function test_the_configured_addresses_override_the_mode(): void
    {
        config([
            'aqtim.mode' => 'production',
            'aqtim.webhook.url' => 'https://hooks.example.com/',
            'aqtim.ticket.url' => 'https://tickets.example.com/',
        ]);

        $this->assertSame('https://hooks.example.com/callback/tickets', aqtim()->webhookUrl());
        $this->assertSame('https://tickets.example.com/ABC123', aqtim()->ticketUrl('ABC123'));
    }

    public function test_only_the_local_mode_holds_the_webhook_back(): void
    {
        foreach (['production' => true, 'test' => true, 'local' => false] as $mode => $sends) {
            config(['aqtim.mode' => $mode]);

            $this->assertSame($sends, aqtim()->mode()->sends());
        }
    }

    public static function environments(): array
    {
        return [
            'production' => ['production', Mode::Production],
            'test' => ['test', Mode::Test],
            'local' => ['local', Mode::Local],
            'testing' => ['testing', Mode::Local],
            'staging' => ['staging', Mode::Local],
        ];
    }

    #[DataProvider('environments')]
    public function test_without_a_mode_the_environment_decides(string $environment, Mode $mode): void
    {
        config(['aqtim.mode' => null]);

        $this->assertSame($mode, (new Aqtim(config(), $environment))->mode());
    }

    public function test_the_configured_mode_wins_over_the_environment(): void
    {
        config(['aqtim.mode' => 'local']);

        $this->assertSame(Mode::Local, (new Aqtim(config(), 'production'))->mode());
    }

    public function test_an_unknown_mode_is_rejected(): void
    {
        config(['aqtim.mode' => 'prod']);

        $this->expectException(InvalidArgumentException::class);

        aqtim()->mode();
    }
}
