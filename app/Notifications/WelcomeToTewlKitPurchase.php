<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeToTewlKitPurchase extends Notification
{
    use Queueable;

    protected $user;
    protected $activation_code;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param $activation_code
     */
    public function __construct(User $user, $activation_code)
    {
        //
        $this->user = $user;
        $this->activation_code = $activation_code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to purchased ' . config('app.name'))
            ->greeting('Hi ' . $this->user->name)
            ->line('Your new account is ready! Thank you for joining ' . config('app.name') . '.')
            ->line('You can activate your account and set your password in there.')
            ->action('Activate', url('activate/' . $this->activation_code))
            ->line('For any help you can contact our customer support at ' . htmlspecialchars(getConfig('support_email')))
            ->line('Thank you for using ' . config('app.name') . '!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
