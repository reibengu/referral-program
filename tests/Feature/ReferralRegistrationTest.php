<?php

namespace Tests\Feature;

use App\Models\ReferralLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReferralRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_referred_users_get_credits()
    {
        $referred_by = User::factory()->create();
        $referral_link = $referred_by->referralLink;

        $this->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $referral_link->code,
        ])->assertCreated();

        $user = User::where('email', 'test@example.com')->first();

        $this->assertEquals(ReferralLink::REFERRED_USER_PAYMENT, $user->credits);

        $this->assertDatabaseHas('referrals', [
            'child_id' => $user->getKey(),
            'parent_id' => $referred_by->getKey(),
            'referral_link_id' => $referral_link->getKey(),
        ]);

        $this->assertEquals(ReferralLink::REFERRAL_USER_PAYMENT_CYCLE - 1, $referral_link->fresh()->remaining_until_payment);
    }

    public function test_non_referred_users_cannot_get_credits()
    {
        $referred_by = User::factory()->create();
        $referral_link = $referred_by->referralLink;

        $this->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();

        $user = User::where('email', 'test@example.com')->first();

        $this->assertEquals(0, $user->credits);

        $this->assertDatabaseMissing('referrals', [
            'child_id' => $user->getKey(),
            'parent_id' => $referred_by->getKey(),
            'referral_link_id' => $referral_link->getKey(),
        ]);

        $this->assertEquals(ReferralLink::REFERRAL_USER_PAYMENT_CYCLE, $referral_link->fresh()->remaining_until_payment);
    }

    public function test_referral_user_gets_credits_on_completed_cycle()
    {
        $referred_by = User::factory()->create();
        $referral_link = $referred_by->referralLink;

        for ($i=0; $i < ReferralLink::REFERRAL_USER_PAYMENT_CYCLE; $i++) {
            $this->post('/api/register', [
                'name' => 'Test User',
                'email' => $i.'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'referral_code' => $referral_link->code,
            ])->assertCreated();

            $user = User::where('email', $i.'test@example.com')->first();

            $this->assertEquals(ReferralLink::REFERRED_USER_PAYMENT, $user->credits);

            $this->assertDatabaseHas('referrals', [
                'child_id' => $user->getKey(),
                'parent_id' => $referred_by->getKey(),
                'referral_link_id' => $referral_link->getKey(),
            ]);
        }

        $this->assertEquals(ReferralLink::REFERRAL_USER_PAYMENT, $referred_by->fresh()->credits);
        $this->assertEquals(ReferralLink::REFERRAL_USER_PAYMENT_CYCLE, $referral_link->fresh()->remaining_until_payment);
    }

    public function test_referral_user_cannot_get_credits_on_incompleted_cycle()
    {
        $referred_by = User::factory()->create();
        $referral_link = $referred_by->referralLink;

        $this->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => $referral_link->code,
        ])->assertCreated();

        $user = User::where('email', 'test@example.com')->first();

        $this->assertEquals(ReferralLink::REFERRED_USER_PAYMENT, $user->credits);

        $this->assertDatabaseHas('referrals', [
            'child_id' => $user->getKey(),
            'parent_id' => $referred_by->getKey(),
            'referral_link_id' => $referral_link->getKey(),
        ]);

        $this->assertEquals(0, $referred_by->fresh()->credits);
        $this->assertEquals(ReferralLink::REFERRAL_USER_PAYMENT_CYCLE - 1, $referral_link->fresh()->remaining_until_payment);
    }
}
