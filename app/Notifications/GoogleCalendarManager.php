<?php


namespace App\Notifications;


use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Carbon;

class GoogleCalendarManager
{
    private $calendar;

    public function __construct($user)
    {
        $client = new Google_Client;
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

        $current = Carbon::now();
        $expired = $user->updated_at->addSeconds($user->expires_in);
        if($current > $expired) {
            $client->setAccessToken($this->getNewToken($user->refresh_token));
        } else {
            $client->setAccessToken($user->token);
        }

        $this->calendar = new Google_Service_Calendar($client);
    }

    private function getNewToken($refreshToken)
    {
        if (!$refreshToken) {
            return 'dummy_token';
        }

        $client = new Google_Client;
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->refreshToken($refreshToken);
        $client->setAccessType('offline');

        return $client->getAccessToken();
    }

    public function addEvent($eventData)
    {
        try {

            $calendarId = 'primary';

            $event = new Google_Service_Calendar_Event([
                'summary' => $eventData->summary,
                'description' => $eventData->description,
                'location' => $eventData->location,
                'colorId' => 3, // Purple, see all: https://stackoverflow.com/questions/11346277/google-calendar-api-php-set-events-color
                'start' => [
                    'dateTime' => $eventData->startDateTime,
                    'timeZone' => env('TIMEZONE', 'Asia/Bangkok'),
                ],
                'end' => [
                    'dateTime' => $eventData->endDateTime,
                    'timeZone' => env('TIMEZONE', 'Asia/Bangkok'),
                ],
                'attendees' => [
                    ['email' => $eventData->studentEmail, 'responseStatus' => "accepted"],
                    ['email' => $eventData->adviserEmail, 'responseStatus' => "accepted"],
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

    public function removeEvent($eventId)
    {
        try {

            $calendarId = 'primary';

            return $this->calendar->events->delete($calendarId, $eventId);

        } catch (\Exception $exception) { }

        return false;
    }
}