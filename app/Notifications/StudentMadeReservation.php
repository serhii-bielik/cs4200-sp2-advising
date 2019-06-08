<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StudentMadeReservation extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentName;
    private $adviserName;
    private $ccEmail;
    private $timeslot;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($timeslot, $studentName, $adviserName, $ccEmail)
    {
        $this->timeslot = $timeslot;
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
            ->line("Your student $this->studentName has made reservation for advising on " .
                $this->timeslot->date . " at " . $this->timeslot->time)
            ->line('You can change reservation and advising status at the panel.')
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
            'message' => "$this->studentName has made reservation for advising on " .
                $this->timeslot->date . " at " . $this->timeslot->time,
        ];
    }
}
