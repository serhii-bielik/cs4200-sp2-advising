<?php


namespace App\Notifications;


class GoogleCalendarEvent
{
    public $summary;
    public $description;
    public $location;
    public $startDateTime;
    public $endDateTime;
    public $studentEmail;
    public $adviserEmail;

    public function __construct($summary, $description, $startDateTime, $endDateTime, $location, $studentEmail, $adviserEmail)
    {
        $this->summary = $summary;
        $this->description = $description;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->location = $location != '' ? $location : "Adviser's Office";
        $this->studentEmail = $studentEmail;
        $this->adviserEmail = $adviserEmail;
    }
}