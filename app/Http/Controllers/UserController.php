<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserInfo()
    {
        $user = auth()->user();

        if ($user) {
            $user->faculty;
            $user->group;
            return $user;
        }

        return response()->json(['error' => 'You are not logged in.'], 400);
    }
}
