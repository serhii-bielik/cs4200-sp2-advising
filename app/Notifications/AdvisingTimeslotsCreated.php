<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdvisingTimeslotsCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $year;
    private $semester;
    private $fromDate;
    private $toDate;

    private $studentName;
    private $ccEmail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($lastPeriod, $studentName, $ccEmail)
    {
        $this->year = $lastPeriod->year;
        $this->semester = $lastPeriod->semester;
        $this->fromDate = $lastPeriod->start_date;
        $this->toDate = $lastPeriod->end_date;

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
            ->line("Your adviser has prepared timeslots for a new advising period: $this->semester/$this->year.")
            ->line("This period will be from $this->fromDate till $this->toDate")
            ->line('Please book timeslot for your advising meeting.')
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
            'message' => 'Your adviser has prepared timeslots for a new advising period.',
        ];
    }
}
