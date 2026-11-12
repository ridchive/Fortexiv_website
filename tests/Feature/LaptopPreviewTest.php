<?php

namespace Tests\Feature;

use Tests\TestCase;

class LaptopPreviewTest extends TestCase
{
    public function test_laptop_preview_renders_the_homepage_inside_a_wide_viewport(): void
    {
        $this->get(route('preview.laptop'))
            ->assertOk()
            ->assertViewIs('preview.laptop')
            ->assertSee(route('home'), false)
            ->assertSee('width: min(1280px, 100%)', false)
            ->assertSee('Pratinjau situs FORTEXIV pada layar laptop');
    }
}
