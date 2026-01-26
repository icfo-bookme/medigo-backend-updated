<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleNotification;

class PusherBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message, $notifications_count;

    public function __construct(string $message)
    {

        $totalCount = DB::table('sales')
            ->where('delivery_status', 1)
            ->count('id');
        $this->message = $totalCount ?? 0;

        $notifications = DB::table('sale_notifications')
            ->where('is_seen', 0)
            ->count('id');
        $this->notifications_count = $notifications ?? 0;
    }

    public function broadcastOn(): array
    {
        return ['my-channel'];
    }

    public function broadcastAs(): string
    {
        return 'my-event';
    }
}
