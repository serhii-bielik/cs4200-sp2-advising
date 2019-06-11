<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AccountSuspended extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $ccEmail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($studentName, $ccEmail)
    {
        $this->studentName = $studentName;
        $this->ccEmail = $ccEmail;
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
        $email = (new MailMessage)
            ->line("Dear $this->studentName.")
            ->line("Your account has been suspended by program director. You no longer able to login advising management panel.")
            ->line("You will be notified once your account will be active again.")
            ->line('Thank you!');

        if ($this->ccEmail) {
            $email->cc($this->ccEmail);
        }

        return $email;
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
            'message' => "Your account has been suspended by program director.",
        ];
    }
}
