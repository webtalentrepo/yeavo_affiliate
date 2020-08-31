<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeToOurSoftware extends Notification
{
    use Queueable;

    protected $user;
    protected $password;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param $password
     */
    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
        try {
            return (new MailMessage)
                ->subject('Welcome to ' . config('app.name') . ' (Your Login information inside)')
                ->greeting('Hi ' . $this->user->name)
                ->line('Your new account is ready! Thank you for joining ' . config('app.name') . '.')
                ->line('Your Login Email is "' . htmlspecialchars($this->user->email) . '" and Password is "' . htmlspecialchars($this->password) . '"')
                ->action('Visit to ' . config('app.name'), url('/'))
                ->line('For any help you can contact our customer support at ' . htmlspecialchars(getConfig('support_email')))
                ->line('Thank you for using ' . config('app.name') . '!');
        } catch (\Exception $e) {
            throw $e;
        }
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
