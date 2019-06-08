<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdviserGotNewStudent extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $adviserName;
    private $ccEmail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($studentName, $adviserName, $ccEmail)
    {
        $this->studentName = $studentName;
        $this->adviserName = $adviserName;
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
        return ['mail', 'database'];
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
            ->line("Dear $this->adviserName.")
            ->line("New student, $this->studentName was assigned to you.")
            ->line("You can see student's details at the panel.")
            ->action('Open Advising Scheduling Panel', url(env('APP_URL', '/')))
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
            'message' => "New student, $this->studentName was assigned to you.",
        ];
    }
}
