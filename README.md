# aqt-im/laravel

Laravel uygulamasındaki bilet olaylarını imzalı webhook ile aqt.im'e gönderir. Paket aqt.im'e özeldir, açık lisanslı değildir.

## Kurulum

```shell
composer require aqt-im/laravel
```

Ortam değişkenleri:

| Değişken | Anlamı |
|----------|--------|
| `AQTIM_WEBHOOK_URL` | aqt.im webhook kökü, örneğin `https://webhook.aqt.im` |
| `AQTIM_WEBHOOK_SECRET` | İmza anahtarı; aqt.im'deki `AQTIVITE_WEBHOOK_SECRET` ile aynı olmalıdır |
| `AQTIM_WEBHOOK_QUEUE` | Olay job'larının kuyruğu; boşsa varsayılan kuyruk |
| `AQTIM_TICKET_URL` | Bilet sayfası kökü, varsayılanı `https://ticket.aqt.im` |

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
