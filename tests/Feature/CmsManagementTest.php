<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use App\Models\Faq;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
    }

    public function test_admin_can_manage_services()
    {
        $this->actingAs($this->admin);

        // Create
        $response = $this->post(route('admin.cms.services.store'), [
            'title' => 'Air Freight Logistics',
            'description' => 'Fastest global delivery options.',
            'icon' => '✈️',
            'status' => 'active',
            'sort_order' => 5,
        ]);
        $response->assertRedirect(route('admin.cms.services.index'));
        $this->assertDatabaseHas('services', ['title' => 'Air Freight Logistics', 'icon' => '✈️']);

        // Update
        $service = Service::first();
        $response = $this->put(route('admin.cms.services.update', $service), [
            'title' => 'Ocean Freight Logistics',
            'description' => 'Slow but cheap delivery.',
            'icon' => '🚢',
            'status' => 'active',
            'sort_order' => 10,
        ]);
        $response->assertRedirect(route('admin.cms.services.index'));
        $this->assertDatabaseHas('services', ['title' => 'Ocean Freight Logistics', 'icon' => '🚢']);

        // Delete
        $response = $this->delete(route('admin.cms.services.destroy', $service));
        $response->assertRedirect(route('admin.cms.services.index'));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_admin_can_manage_faqs()
    {
        $this->actingAs($this->admin);

        // Create
        $response = $this->post(route('admin.cms.faqs.store'), [
            'question' => 'What is SAEE?',
            'answer' => 'SAEE is a delivery network platform.',
            'category' => 'general',
            'status' => 'active',
            'sort_order' => 0,
        ]);
        $response->assertRedirect(route('admin.cms.faqs.index'));
        $this->assertDatabaseHas('faqs', ['question' => 'What is SAEE?']);

        // Update
        $faq = Faq::first();
        $response = $this->put(route('admin.cms.faqs.update', $faq), [
            'question' => 'What is SAEE Logistics?',
            'answer' => 'SAEE is a premier delivery network platform.',
            'category' => 'general-about',
            'status' => 'active',
            'sort_order' => 1,
        ]);
        $response->assertRedirect(route('admin.cms.faqs.index'));
        $this->assertDatabaseHas('faqs', ['question' => 'What is SAEE Logistics?', 'category' => 'general-about']);

        // Delete
        $response = $this->delete(route('admin.cms.faqs.destroy', $faq));
        $response->assertRedirect(route('admin.cms.faqs.index'));
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_update_site_settings()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.settings.site.update'), [
            'site_name' => 'SAEE Express Jordan',
            'site_email' => 'support@saee.com.jo',
            'site_phone' => '+962799999999',
            'site_address' => 'Abdali Tower, Amman, Jordan',
            'meta_title' => 'SAEE Jordan | Delivery & Logistics',
            'meta_description' => 'Top courier service in Jordan.',
            'meta_keywords' => 'jordan, express, saee',
            'social_facebook' => 'https://facebook.com/saee-jo',
            'social_twitter' => 'https://twitter.com/saee-jo',
            'social_instagram' => 'https://instagram.com/saee-jo',
            'social_linkedin' => 'https://linkedin.com/saee-jo',
        ]);

        $response->assertRedirect(route('admin.settings.site.index'));
        
        $this->assertEquals('SAEE Express Jordan', SiteSetting::getVal('site_name'));
        $this->assertEquals('support@saee.com.jo', SiteSetting::getVal('site_email'));
        $this->assertEquals('+962799999999', SiteSetting::getVal('site_phone'));
        $this->assertEquals('https://facebook.com/saee-jo', SiteSetting::getVal('social_facebook'));
    }

    public function test_public_pages_and_homepage_render_cms_content()
    {
        // Setup database contents
        Service::create([
            'title' => 'Custom Courier Service',
            'description' => 'Dynamic description here.',
            'icon' => '⚡',
            'status' => 'active',
            'sort_order' => 2
        ]);

        Faq::create([
            'question' => 'Can I return packages?',
            'answer' => 'Yes, within 2 days.',
            'category' => 'Returns',
            'status' => 'active',
            'sort_order' => 1
        ]);

        SiteSetting::setVal('site_name', 'Dynamic SAEE Jordan');
        SiteSetting::setVal('site_phone', '+962777777777');
        SiteSetting::setVal('meta_title', 'Jordan Premier Logistics Service');

        // Request Homepage
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);
        $response->assertSee('Custom Courier Service');
        $response->assertSee('Can I return packages?');
        $response->assertSee('Dynamic SAEE Jordan');
        $response->assertSee('+962777777777');
    }
}
