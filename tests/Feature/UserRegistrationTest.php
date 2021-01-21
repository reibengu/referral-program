<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_register()
    {
        $this->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();
    }

    public function test_validation_rules_on_register()
    {
        $this->post('/api/register')->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_users_can_register_with_valid_referral_code()
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
    }

    public function test_users_cannot_register_with_invalid_referral_code()
    {
        $this->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'referral_code' => 'abc12',
        ])->assertNotFound();
    }
}
