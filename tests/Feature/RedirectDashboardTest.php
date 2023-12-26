<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectDashboardTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function testRedirectDashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
        $response->assertRedirectToRoute('filament.admin.pages.dashboard');
    }
}
