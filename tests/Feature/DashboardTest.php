<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('email', 'admin@mbg.com')->first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'admin@mbg.com',
                'role' => 'admin_pusat'
            ]);
        }

        $response = $this->actingAs($user)->get('/dashboard');
        
        // Output body if it fails to see the error details
        if ($response->status() !== 200) {
            fwrite(STDERR, $response->getContent() . "\n");
        }

        $response->assertStatus(200);
    }
}
