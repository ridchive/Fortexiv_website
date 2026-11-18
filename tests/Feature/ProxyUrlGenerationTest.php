<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProxyUrlGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_links_use_https_host_from_trusted_local_proxy(): void
    {
        $response = $this->get('/', [
            'Host' => '127.0.0.1:8000',
            'X-Forwarded-Host' => 'fortexiv-preview.example.test',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ]);

        $response->assertOk()
            ->assertSee('https://fortexiv-preview.example.test/catalog', false);
    }
}
