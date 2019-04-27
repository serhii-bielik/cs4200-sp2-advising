<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\UserGroup;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    public function logout() {
        auth()->logout();
        return redirect()->to(env('APP_URL', '/'));
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect(env('APP_URL', '/'));
        }

        if (User::count() == 0) {
            $newUser = new User;
            $newUser->first_name = $user->name;
            $newUser->au_id = '99999';
            $newUser->email = $user->email;
            $newUser->google_id = $user->id;
            $newUser->avatar = $user->avatar;
            $newUser->avatar_original = $user->avatar_original;
            $newUser->group_id = UserGroup::Admin;
            $newUser->save();

            auth()->login($newUser, true);

            session()->flash('message', "Admin user was successfully added.");
            session()->flash('alert-class', 'alert-success');

            return redirect()->to('admin/advisers');
        } else {
            $existingUser = User::where('email', $user->email)->first();

            if($existingUser){

                $existingUser->google_id = $user->id;
                $existingUser->avatar = $user->avatar;
                $existingUser->avatar_original = $user->avatar_original;
                $existingUser->save();

                auth()->login($existingUser, true);

                if ($existingUser->group_id == UserGroup::Admin) {
                    return redirect()->to('admin/advisers');
                } else if ($existingUser->group_id == UserGroup::Student) {
                    return redirect()->to(env('APP_URL', '/'));
                } else {
                    return redirect()->to(env('APP_URL', '/'));
                }

            } else {
                session()->flash('message', "User with {$user->email} was not found in the system.");
                session()->flash('alert-class', 'alert-danger');
            }
        }

        return redirect()->to(env('APP_URL', '/'));
    }
}
