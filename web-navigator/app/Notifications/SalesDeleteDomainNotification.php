<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SalesDeleteDomainNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $domaindata = [];
    public function __construct($d)
    {
        //
        $this->domaindata = $d;
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
        return (new MailMessage)
            ->subject('Navibot - Domain Deleted!')
            ->line('Hello! '.$this->domaindata->name.',')
            ->line('The following domain and its urls has been deleted by NaviBot administrator')
            ->line(new HtmlString('Domain : <strong>'.$this->domaindata->url.'</strong>'))
            ->action('Click here to login', url('/login'))
            ->line('This is a system generated email, do not reply to this email');
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
