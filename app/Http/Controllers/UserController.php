<?php

namespace App\Http\Controllers;

use App\Faculty;
use App\Period;
use App\Reservation;
use App\Timeslot;
use DateTime;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('getUserInfo');
    }

    public function getUserInfo()
    {
        $user = auth()->user();

        if ($user) {
            $user->faculty;
            $user->group;
            return $user;
        }

        return response()->json(['error' => 'You are not logged in.'], 401);
    }

    public function getNotifications()
    {
        return auth()->user()->unreadNotifications;
    }

    public function readNotification()
    {
        $notificationId = \request('notification_id');

        if($notificationId) {
            $notification = auth()->user()->unreadNotifications->where('id', $notificationId);

            if ($notification) {
                $notification->markAsRead();

                return ['status' => 'success',
                    'message' => 'Notification read'];
            }
        }

        return response()->json(['error' => 'Wrong notification ID.'], 400);
    }

    public function readAllNotifications()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return ['status' => 'success',
            'message' => 'Notifications read'];
    }

    public function period()
    {
        $lastPeriod = Period::orderBy('start_date', 'desc')
            ->select('semester', 'year', 'start_date', 'end_date')
            ->first();

        if (!$lastPeriod) {
            return response()->json(['error' => 'There is no period created yet.'], 400);
        }

        return $lastPeriod;
    }

    public function faculties()
    {
        return Faculty::all();
    }

    public function settings()
    {
        $settings = [];

        $user = auth()->user();

        $settings['phone'] = $user->phone;
        $settings['office'] = $user->office;
        $settings['is_notification'] = $user->is_notification;
        $settings['is_allow_flexible_timeslots'] = $user->is_allow_flexible_timeslots;
        $settings['interval'] = $user->interval;

        return $settings;
    }

    public function setSettings()
    {
        $settings = [];

        $user = auth()->user();

        $phone = request('phone');
        if (isset($phone)) {
            $user->phone = $phone;
        }

        if (!$user->isStudent()) {
            $office = request('office');
            if (isset($office)) {
                $user->office = $office;
            }

            $isAllowFlexibleTimeslots = request('is_allow_flexible_timeslots');
            if (isset($isAllowFlexibleTimeslots)) {
                $user->is_allow_flexible_timeslots = $isAllowFlexibleTimeslots;
            }
        }

        $isNotification = request('is_notification');
        if (isset($isNotification)) {
            $user->is_notification = $isNotification;
        }

        if (!$user->isStudent()) {

            $interval = request('interval');
            if (isset($interval)) {
                if ($user->interval != $interval) {

                    $lastPeriod = Period::orderBy('start_date', 'desc')->first();

                    Timeslot::where('adviser_id', $user->id)
                        ->where('period_id', $lastPeriod->id)
                        ->delete();

                    $user->interval = $interval;
                }
            }
        }

        $user->save();

        $settings['phone'] = $user->phone;
        $settings['office'] = $user->office;
        $settings['is_notification'] = $user->is_notification;
        $settings['interval'] = $user->interval;

        return $settings;
    }
}
