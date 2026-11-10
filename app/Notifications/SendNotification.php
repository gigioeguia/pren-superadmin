<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification
{
    use Queueable;
    private $notify;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notify)
    {
        $this->notify = $notify;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->notify['id'],
            'admin_url' => $this->notify['admin_url'],
            'landlord_url' => $this->notify['landlord_url'],
            'admin_msg' => $this->notify['admin_msg'],
            'landlord_msg' => $this->notify['landlord_msg'],
            'tenant_msg' => $this->notify['tenant_msg'],
        ];
    }

}
