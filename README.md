# aqt-im/laravel

Laravel uygulamasındaki bilet olaylarını imzalı webhook ile aqt.im'e gönderir. Paket aqt.im'e özeldir, açık lisanslı değildir.

## Kurulum

```shell
composer require aqt-im/laravel
```

Ortam değişkenleri:

| Değişken | Anlamı |
|----------|--------|
| `AQTIM_MODE` | `production`, `test` veya `local`; tanımlı değilse uygulamanın ortamından türetilir |
| `AQTIM_WEBHOOK_SECRET` | İmza anahtarı; aqt.im'deki `AQTIVITE_WEBHOOK_SECRET` ile aynı olmalıdır. `local` modda gerekmez |

## Modlar

Adresler moddan gelir:

| Mod | Webhook | Bilet sayfası | Gönderim |
|-----|---------|---------------|----------|
| `production` | `https://webhook.aqt.im` | `https://ticket.aqt.im` | Gönderir |
| `test` | `https://webhook.test.aqt.im` | `https://ticket.test.aqt.im` | Gönderir |
| `local` | `https://webhook.local.aqt.im` | `https://ticket.local.aqt.im` | Göndermez |

`local` modda olay yine kuyruğa girer ve payload kurulur; HTTP isteği atılmaz, gidecek istek `debug` seviyesinde log'a yazılır.

Bir modun adresini ezmek için config yayınlanır ve `webhook.url` veya `ticket.url` doğrudan yazılır:

```shell
php artisan vendor:publish --tag=aqtim-config
```

`AQTIM_MODE` tanımlı değilse mod `APP_ENV`'den gelir: `production` ortamı `production`, `test` ortamı `test` modunu, geri kalan her ortam (`local`, `testing`, `staging` …) `local` modunu seçer.

`AQTIM_MODE` tanımlıysa ortama bakılmaz. Tanınmayan bir değer uygulama açılırken `InvalidArgumentException` fırlatır.

## Kuyruk

Olay job'ı, `spatie/laravel-webhook-server`'ın `webhook-server.queue` kuyruğunda çalışır; payload'ın kurulması ve HTTP isteği aynı kuyruktan geçer. Ayrı bir kuyruk için config'teki `webhook.queue` yazılır.

## Kullanım

Bilet modeli `AqtIm\Laravel\Contracts\Ticket` sözleşmesini uygular ve `SendsTicketEvents` trait'ini kullanır:

```php
class Ticket extends Model implements \AqtIm\Laravel\Contracts\Ticket
{
    use \AqtIm\Laravel\Concerns\SendsTicketEvents;

    public function aqtimPnrCode(): string
    {
        return $this->pnr_code;
    }

    public function aqtimPayload(): array
    {
        return $this->load([/* ... */])->toArray();
    }
}
```

Trait `created`, `updated`, `deleted`, `restored` ve varsa `approvalChanged` olaylarını dinler. Her olay transaction commit edildikten sonra kuyruğa girer; payload job çalıştığında modelin güncel halinden üretilir.

Bilet sayfasının adresi `aqtim()->ticketUrl($pnrCode)` ile alınır.

## Test

```shell
docker compose run --rm php85
```
