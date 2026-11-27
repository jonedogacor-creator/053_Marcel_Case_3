<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_is_sent_to_the_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_and_registration_pages_render(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('KeranjangKu')
            ->assertSee('Perubahan Kecil Hari Ini Adalah Faktor Sukses Mu di Masa Depan');
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('KeranjangKu')
            ->assertSee('Perubahan Kecil Hari Ini Adalah Faktor Sukses Mu di Masa Depan');
    }
}
