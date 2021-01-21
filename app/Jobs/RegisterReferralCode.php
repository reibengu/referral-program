<?php

namespace App\Jobs;

use App\Models\ReferralLink;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegisterReferralCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $referral_code;
    private $user;

    public function __construct(string $referral_code, User $user)
    {
        $this->referral_code = $referral_code;
        $this->user = $user;
    }

    public function handle()
    {
        $referred_by = $this->getReferredByUser($this->referral_code);
        $referral_link = $referred_by->referralLink;

        $referred_by->referrals()->create([
            'child_id' => $this->user->getKey(),
            'referral_link_id' => $referral_link->getKey(),
        ]);

        if ($referral_link->remaining_until_payment == 1) {
            $referred_by->increment('credits', ReferralLink::REFERRAL_USER_PAYMENT);

            $referral_link->update(['remaining_until_payment' => ReferralLink::REFERRAL_USER_PAYMENT_CYCLE]);
        } else {
            $referral_link->decrement('remaining_until_payment', 1);
        }

        $this->user->increment('credits', ReferralLink::REFERRED_USER_PAYMENT);
    }

    protected function getReferredByUser(string $referral_code): User
    {
        return User::with('referralLink')
            ->whereHas('referralLink', function ($query) use ($referral_code) {
                $query->where('code', $referral_code);
            })
            ->firstOrFail();
    }
}
