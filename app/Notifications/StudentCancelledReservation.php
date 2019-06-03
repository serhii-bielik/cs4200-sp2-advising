<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StudentCancelledReservation extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $adviserName;
    private $timeslotDate;
    private $timeslotTime;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($timeslotDate, $timeslotTime, $studentName, $adviserName)
    {
        $this->timeslotDate = $timeslotDate;
        $this->timeslotTime = $timeslotTime;
        $this->studentName = $studentName;
        $this->adviserName = $adviserName;
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
        return (new MailMessage)
                    ->line("Dear $this->adviserName.")
                    ->line("Your student $this->studentName has canceled reservation for advising on " .
                        $this->timeslotDate . " at " . $this->timeslotTime)
                    ->line('You can check your schedule at the panel.')
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
            'message' => "Your student $this->studentName has canceled reservation for advising on " .
                $this->timeslotDate . " at " . $this->timeslotTime,
        ];
    }
}
