<?php

namespace Tests\Feature;

use App\Mail\ConfirmationEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use RefreshDatabase;

    public function testLoginSendsConfirmationEmail()
    {
        Mail::fake();

        $this->postJson('/v1/auth/login', ['email' => 'ada@lovelace.com'])
             ->assertStatus(200);

        Mail::assertSent(ConfirmationEmail::class);

        return Mail::sent(ConfirmationEmail::class)->first()->url;
    }

    public function testConfirmWithMissingSignatureFails()
    {
        $this->post('/v1/auth/confirm')
             ->assertJsonPath('error.code', 'KI-AUTH-0001');
    }

    /**
     * @depends testLoginSendsConfirmationEmail
     */
    public function testConfirmCreatesNewUser($url)
    {
        $token = $this->post($url)->assertStatus(200)->getOriginalContent()['token'];

        $this->assertDatabaseHas('users', ['email' => 'ada@lovelace.com']);

        return $token;
    }

    /**
     * @depends testLoginSendsConfirmationEmail
     */
    public function testRespondsToCookieToken($url)
    {
        $this->post($url);

        $this->get('/v1/auth/me')
             ->assertStatus(201);
        // This route actually returns a 200 code status, but, since we create
        // and return this User instance in the same test, Laravel thinks both
        // actions happened in the same request, and then automatically returns
        // a 201: Created response code. More on:
        // https://github.com/laravel/framework/issues/25868
    }

    public function testCheckAvailableUsername()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
             ->post('/v1/auth/check', ['username' => 'adalovelace'])
             ->assertOk();
    }

    public function testUpdateUserDetails()
    {
        $user = factory(User::class)->create(['email' => 'not@ada.com']);

        $this->actingAs($user)
             ->patch('/v1/auth/me', [
                 'name' => 'Ada Lovelace',
                 'username' => 'adalovelace',
                 'email' => 'ada@lovelace.com',
             ])
             ->assertOk();
    }

    /**
     * @depends testLoginSendsConfirmationEmail
     */
    public function testLogoutUser($url)
    {
        $this->post($url);

        $this->post('/v1/auth/logout')
             ->assertOk();
    }

    /**
     * @depends testLoginSendsConfirmationEmail
     */
    public function testRefreshValidToken($url)
    {
        $this->post($url);

        $this->post('/v1/auth/refresh')
             ->assertOk();
    }

    /**
     * @depends testLoginSendsConfirmationEmail
     */
    public function testRefreshExpiredToken($url)
    {
        config()->set('jwt.ttl', 0);

        $this->post($url);

        $this->post('/v1/auth/refresh')
             ->assertJsonPath('error.code', 'KI-AUTH-0002');
    }
}
