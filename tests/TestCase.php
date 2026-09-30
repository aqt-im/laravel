<?php

namespace AqtIm\Laravel\Tests;

use AqtIm\Laravel\AqtimServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\WebhookServer\WebhookServerServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('pnr_code');
            $table->string('name');
            $table->string('approval_status')->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            WebhookServerServiceProvider::class,
            AqtimServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('queue.default', 'sync');

        $app['config']->set('aqtim.webhook.url', 'https://webhook.aqt.im/');
        $app['config']->set('aqtim.webhook.secret', 'secret');
    }
}
