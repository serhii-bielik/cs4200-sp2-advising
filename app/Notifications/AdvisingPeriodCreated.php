<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdvisingPeriodCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $year;
    private $semester;
    private $fromDate;
    private $toDate;
    private $adviserName;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($lastPeriod, $adviserName)
    {
        $this->year = $lastPeriod->year;
        $this->semester = $lastPeriod->semester;
        $this->fromDate = $lastPeriod->start_date;
        $this->toDate = $lastPeriod->end_date;

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
                    ->line("The program director has created a new advising period for $this->semester/$this->year.")
                    ->line("This period will be from $this->fromDate till $this->toDate")
                    ->line('Please specify your advising timeslots for students.')
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
            'message' => 'The director has created new advising period.',
        ];
    }
}
