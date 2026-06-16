<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Visiting /lang/ar sets the session locale to 'ar' and redirects back.
     */
    public function test_language_switch_to_arabic_sets_session_and_redirects(): void
    {
        $response = $this->get('/lang/ar');

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'ar');
    }

    /**
     * Visiting /lang/en sets the session locale to 'en' and redirects back.
     */
    public function test_language_switch_to_english_sets_session_and_redirects(): void
    {
        $response = $this->get('/lang/en');

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
    }

    /**
     * An unknown locale is silently ignored — session stays unchanged.
     */
    public function test_unknown_locale_does_not_set_session(): void
    {
        $response = $this->get('/lang/fr');

        $response->assertRedirect();
        $response->assertSessionMissing('locale');
    }

    /**
     * After switching to Arabic the session should persist 'ar' locale.
     * We verify the middleware correctly restores locale from session.
     */
    public function test_arabic_session_locale_is_read_by_middleware(): void
    {
        // Set Arabic session and hit the admin login page (no DB dependency)
        $response = $this->withSession(['locale' => 'ar'])->get('/admin/login');

        $response->assertStatus(200);
        // The layout sets dir="rtl" when locale is ar
        $response->assertSee('dir="rtl"', false);
    }

    /**
     * English locale renders ltr direction on the admin login page.
     */
    public function test_english_session_locale_renders_ltr_on_admin_login(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
    }
}
