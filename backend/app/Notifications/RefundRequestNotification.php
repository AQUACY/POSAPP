<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RefundRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $refund;

    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'refund_request',
            'refund_id' => $this->refund->id,
            'sale_number' => $this->refund->sale->sale_number,
            'amount' => $this->refund->total_amount,
            'reason' => $this->refund->reason,
            'requested_by' => $this->refund->user->name,
            'created_at' => $this->refund->created_at,
            'message' => "New refund request for Sale #{$this->refund->sale->sale_number} - $" . number_format($this->refund->total_amount, 2)
        ];
    }
} 