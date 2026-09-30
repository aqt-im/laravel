<?php

namespace AqtIm\Laravel\Tests\Fixtures;

use AqtIm\Laravel\Concerns\SendsTicketEvents;
use AqtIm\Laravel\Contracts\Ticket as TicketContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model implements TicketContract
{
    use SendsTicketEvents, SoftDeletes;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('approved', fn (Builder $builder) => $builder->where('approval_status', 'approved'));
    }

    public function aqtimPnrCode(): string
    {
        return $this->pnr_code;
    }

    public function aqtimPayload(): array
    {
        return $this->toArray();
    }

    public function approve(): void
    {
        $this->newQueryWithoutScopes()->whereKey($this->getKey())->toBase()->update(['approval_status' => 'approved']);

        $this->approval_status = 'approved';

        $this->fireModelEvent('approvalChanged', false);
    }
}
