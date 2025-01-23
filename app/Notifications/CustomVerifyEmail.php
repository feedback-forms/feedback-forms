<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class CustomVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        Log::info('Sending verification email', [
            'user' => $notifiable->name,
            'email' => $notifiable->email,
            'url' => $verificationUrl
        ]);

        return (new MailMessage)
            ->subject(__('notification.verify_email.subject'))
            ->markdown('vendor.notifications.email', [
                'greeting' => __('notification.verify_email.greeting', ['name' => $notifiable->name]),
                'introLines' => [__('notification.verify_email.intro')],
                'actionText' => __('notification.verify_email.action'),
                'actionUrl' => $verificationUrl,
                'level' => 'default',
                'outroLines' => [__('notification.verify_email.outro')],
                'salutation' => __('notification.verify_email.salutation') . "\n" . config('app.name') . ' Team'
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
