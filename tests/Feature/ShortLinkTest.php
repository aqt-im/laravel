<?php

namespace AqtIm\Laravel\Tests\Feature;

use AqtIm\Laravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ShortLinkTest extends TestCase
{
    public static function modes(): array
    {
        return [
            'production' => ['production', 'https://aqt.im'],
            'test' => ['test', 'https://test.aqt.im'],
            'local' => ['local', 'https://local.aqt.im'],
        ];
    }

    #[DataProvider('modes')]
    public function test_each_mode_has_its_own_short_link_root(string $mode, string $root): void
    {
        config(['aqtim.mode' => $mode]);

        $this->assertSame("{$root}/e/duman-konseri", aqtim()->shortLink()->event('duman-konseri'));
    }

    public function test_each_kind_has_its_own_prefix(): void
    {
        config(['aqtim.mode' => 'production']);

        $shortLink = aqtim()->shortLink();

        $this->assertSame('https://aqt.im/u/mehmet', $shortLink->user('mehmet'));
        $this->assertSame('https://aqt.im/a/sezen-aksu', $shortLink->artist('sezen-aksu'));
        $this->assertSame('https://aqt.im/o/zorlu-psm', $shortLink->organizer('zorlu-psm'));
        $this->assertSame('https://aqt.im/e/duman-konseri', $shortLink->event('duman-konseri'));
        $this->assertSame('https://aqt.im/v/inogarden', $shortLink->venue('inogarden'));
    }

    public function test_a_qr_link_carries_the_qr_marker(): void
    {
        config(['aqtim.mode' => 'production']);

        $this->assertSame('https://aqt.im/e/duman-konseri?qr', aqtim()->shortLink(qr: true)->event('duman-konseri'));
    }

    public function test_the_configured_root_overrides_the_mode(): void
    {
        config(['aqtim.mode' => 'production', 'aqtim.short.url' => 'https://go.example.com/']);

        $this->assertSame('https://go.example.com/e/duman-konseri', aqtim()->shortLink()->event('duman-konseri'));
    }

    public function test_the_slug_is_encoded(): void
    {
        config(['aqtim.mode' => 'production']);

        $this->assertSame('https://aqt.im/u/a%20b%2Fc', aqtim()->shortLink()->user('a b/c'));
    }
}
