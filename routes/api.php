<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', 'RegistrationController@store');

Route::middleware('auth:api')->get('profile', function (Request $request) {
    return $request->user()->load(['referralLink', 'referrals.user:id,name,email']);
});
