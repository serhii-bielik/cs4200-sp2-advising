<?php


namespace App\Notifications;


use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Carbon;

class GoogleCalendarManager
{
    private $calendar;

    public function __construct($token)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

        $client->setAccessToken($token);
        $this->calendar = new Google_Service_Calendar($client);
    }

    public function addEvent($eventData)
    {
        try {

            $calendarId = 'primary';

            $event = new Google_Service_Calendar_Event([
                'summary' => $eventData->summary,
                'description' => $eventData->description,
                'location' => $eventData->location,
                'start' => [
                    'dateTime' => $eventData->startDateTime,
                    'timeZone' => env('TIMEZONE', 'Asia/Bangkok'),
                ],
                'end' => [
                    'dateTime' => $eventData->endDateTime,
                    'timeZone' => env('TIMEZONE', 'Asia/Bangkok'),
                ],
                'attendees' => [
                    ['email' => $eventData->studentEmail],
                    ['email' => $eventData->adviserEmail],
                ],

                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'popup', 'minutes' => 24 * 60],
                        ['method' => 'email', 'minutes' => 24 * 60],
                        ['method' => 'popup', 'minutes' => 15],
                    ],
                ],
            ]);

            return $this->calendar->events->insert($calendarId, $event);

        } catch (\Exception $exception) { }

        return false;
    }
}