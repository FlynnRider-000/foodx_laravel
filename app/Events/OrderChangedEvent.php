<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $oldStatus;

    public $updatedOrder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($oldStatus, $updatedOrder)
    {
        $this->oldStatus = $oldStatus;
        $this->updatedOrder = $updatedOrder;
    }
}
