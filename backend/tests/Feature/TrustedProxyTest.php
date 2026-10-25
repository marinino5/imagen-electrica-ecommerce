<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_urls_use_https_when_the_proxy_says_so(): void
    {
        $this->get(route('admin.login'), ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'tienda.example.com'])
            ->assertOk()
            ->assertSee('https://tienda.example.com/admin', false);
    }
}
