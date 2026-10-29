<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobilePreviewTest extends TestCase
{
    public function test_mobile_preview_renders_the_homepage_inside_a_phone_viewport(): void
    {
        $this->get(route('preview.mobile'))
            ->assertOk()
            ->assertViewIs('preview.mobile')
            ->assertSee(route('home'), false)
            ->assertSee('width: 410px', false)
            ->assertSee('Pratinjau situs FORTEXIV pada layar ponsel');
    }
}
