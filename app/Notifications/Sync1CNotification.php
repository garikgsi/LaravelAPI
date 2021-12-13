<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class Sync1CNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $logs;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($logs = null)
    {
        $this->logs = $logs;
        // $this->queue = 'default';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $msg = new MailMessage;
        if ($this->logs) {
            foreach ($this->logs as $log_line) {
                $msg->line($log_line);
            }
        } else {
            $msg->line('Синхронизация выполнена ' . date('d.m.Y в H:i:s (UTC)'));
        }
        return $msg;
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
            //
        ];
    }
}