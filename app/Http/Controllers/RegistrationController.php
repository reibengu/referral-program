<?php

namespace App\Http\Controllers;

use App\Jobs\RegisterReferralCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class RegistrationController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|confirmed|min:8',
            'referral_code' => 'string|size:5',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('referral_code')) {
            RegisterReferralCode::dispatch($request->referral_code, $user);
        }

        return response()->json([], 201);
    }
}
