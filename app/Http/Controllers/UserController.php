<?php

namespace App\Http\Controllers;

use App\User;
use App\UserGroup;
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

    public function settings()
    {
        $settings = [];

        $user = auth()->user();

        $settings['phone'] = $user->phone;
        $settings['office'] = $user->office;
        $settings['is_notification'] = $user->is_notification;
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
        }

        $isNotification = request('is_notification');
        if (isset($isNotification)) {
            $user->is_notification = $isNotification;
        }

        if (!$user->isStudent()) {
            $interval = request('interval');
            if (isset($interval)) {
                $user->interval = $interval;
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
