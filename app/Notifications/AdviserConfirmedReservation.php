<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdviserConfirmedReservation extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $timeslotDate;
    private $timeslotTime;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($timeslotDate, $timeslotTime, $studentName)
    {
        $this->timeslotDate = $timeslotDate;
        $this->timeslotTime = $timeslotTime;
        $this->studentName = $studentName;
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
            ->line("Dear $this->studentName.")
            ->line("Your adviser has confirmed your reservation for advising on " .
                $this->timeslotDate . " at " . $this->timeslotTime)
            ->line('You can see details at the panel.')
            ->action('Open Advising Scheduling Panel', url(env('APP_URL', '/')))
            ->line('Thank you!');
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
            'message' => "Your adviser has confirmed your reservation for advising on " .
                $this->timeslotDate . " at " . $this->timeslotTime
        ];
    }
}
