<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    use HasFactory;

    protected $guarded = [];

    const REFERRED_USER_PAYMENT = 1000;

    const REFERRAL_USER_PAYMENT = 1000;
    const REFERRAL_USER_PAYMENT_CYCLE = 5;
}
