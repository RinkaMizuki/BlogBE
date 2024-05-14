<?php

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;

if (!function_exists('getLoginUser')) {
    function getLoginUser()
    {
        if (Cookie::get('sessionId')) {
            $sessionIdFromCookie = Cookie::get('sessionId');
            Session::setId($sessionIdFromCookie);
            $userId = Session::get('loginId');
        } else {
            $userId = Session::get('loginId');
        }
        $user = User::find($userId);
        return $user;
    }
}
