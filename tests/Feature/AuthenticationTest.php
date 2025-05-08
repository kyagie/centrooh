<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\OneTimePassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    /**
     * Test requesting an OTP.
     *
     * @return void
     */
    public function test_can_request_otp()
    {
        $phoneNumber = '+256712345678';
        
        $response = $this->postJson('/api/auth/request-otp', [
            'phone_number' => $phoneNumber
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'phone_number'
            ]);
            
        $this->assertDatabaseHas('one_time_passwords', [
            'phone_number' => preg_replace('/[^0-9]/', '', $phoneNumber),
            'verified' => false
        ]);
    }
    
    /**
     * Test verifying an OTP with a non-agent phone number.
     *
     * @return void
     */
    public function test_cannot_verify_otp_for_non_agent()
    {
        $phoneNumber = '+256712345678';
        
        // Create an OTP manually
        $otp = OneTimePassword::createForPhone($phoneNumber);
        
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => $phoneNumber,
            'otp_code' => $otp->otp_code,
            'device_name' => 'Test Device'
        ]);
        
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Phone number is not associated with any agent',
                'status' => 'unregistered'
            ]);
    }
    
    /**
     * Test successful authentication flow.
     *
     * @return void
     */
    public function test_can_authenticate_with_otp()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create an agent
        $agent = Agent::factory()->create([
            'phone_number' => '256712345678',
            'user_id' => $user->id
        ]);
        
        // Create an OTP
        $otp = OneTimePassword::createForPhone($agent->phone_number);
        
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => $agent->phone_number,
            'otp_code' => $otp->otp_code,
            'device_name' => 'Test Device'
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'status',
                'agent',
                'token',
                'token_type',
                'expires_at'
            ]);
        
        $this->assertEquals('authenticated', $response->json('status'));
    }
    
    /**
     * Test that authenticated users can access their profile.
     *
     * @return void
     */
    public function test_can_get_agent_profile_with_token()
    {
        // Create a user with an agent profile
        $user = User::factory()->create();
        $agent = Agent::factory()->create([
            'user_id' => $user->id
        ]);
        
        // Create a token for the user
        $token = $user->createToken('Test Token')->plainTextToken;
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'agent',
                'user'
            ]);
    }
    
    /**
     * Test that users can logout.
     *
     * @return void
     */
    public function test_can_logout()
    {
        // Create a user with an agent profile
        $user = User::factory()->create();
        $agent = Agent::factory()->create([
            'user_id' => $user->id
        ]);
        
        // Create a token for the user
        $token = $user->createToken('Test Token')->plainTextToken;
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ]);
        
        // Assert that the token is no longer in the database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
