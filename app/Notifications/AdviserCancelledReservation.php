<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdviserCancelledReservation extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $ccEmail;
    private $timeslotDate;
    private $timeslotTime;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($timeslotDate, $timeslotTime, $studentName, $ccEmail)
    {
        $this->timeslotDate = $timeslotDate;
        $this->timeslotTime = $timeslotTime;
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
                    ->line("Dear $this->studentName.")
                    ->line("Your adviser has canceled reservation for advising on " .
                        $this->timeslotDate . " at " . $this->timeslotTime)
                    ->line('You can make new reservation at the panel.')
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
            'message' => "Your adviser has canceled reservation for advising on " .
                $this->timeslotDate . " at " . $this->timeslotTime
        ];
    }
}
