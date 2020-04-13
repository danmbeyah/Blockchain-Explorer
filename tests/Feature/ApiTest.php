<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ApiTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;
    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'name'    => 'Test User', 
            'email'    => 'test1@email.com',
            'password' => '123456'
        ]);

        $this->user->save();

        $response = $this->post('api/login', [
            'email'    => 'test1@email.com',
            'password' => '123456'
        ]);

        $this->token = $response->getData()->access_token;
    }

    /** @test */
    public function createWallet()
    {
        $response = $this->post('api/wallet', [
            'label'    => 'Bitcoin Wallet',
            'email'    => 'test2@email.com',
            'pass_phrase' => 'My Pass Phrase',
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function getWallet()
    {
        $response = $this->get('api/wallet/39997efa-c2ab-4e55-85ba-6a190a77d0f5', [
            'pass_phrase' => 'Wrong pass phrase',
            'Authorization' => 'Bearer ' . $this->token
        ]);

        $response->assertStatus(400);
    }
}
