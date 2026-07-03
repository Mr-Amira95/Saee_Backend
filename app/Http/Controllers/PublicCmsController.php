<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Banner;
use App\Models\Service;
use App\Models\Faq;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class PublicCmsController extends Controller
{
    /**
     * Render the public landing homepage.
     */
    public function home()
    {
        $banners = Banner::where('status', 'active')->orderBy('sort_order')->get();
        $services = Service::where('status', 'active')->orderBy('sort_order')->get();
        $faqs = Faq::where('status', 'active')->orderBy('sort_order')->get();

        $settings = [
            'site_name'        => SiteSetting::getVal('site_name', 'SAEE Logistics'),
            'site_email'       => SiteSetting::getVal('site_email', 'info@saee.com.jo'),
            'site_phone'       => SiteSetting::getVal('site_phone', '+962 6 123 4567'),
            'site_address'     => SiteSetting::getVal('site_address', 'Amman, Jordan'),
            'meta_title'       => SiteSetting::getVal('meta_title', 'SAEE Logistics - Premier Delivery Solutions'),
            'meta_description' => SiteSetting::getVal('meta_description', 'SAEE is a premier delivery and logistics network connecting drivers and clients across the country.'),
            'social_facebook'  => SiteSetting::getVal('social_facebook', 'https://facebook.com'),
            'social_twitter'   => SiteSetting::getVal('social_twitter', 'https://twitter.com'),
            'social_instagram' => SiteSetting::getVal('social_instagram', 'https://instagram.com'),
            'social_linkedin'  => SiteSetting::getVal('social_linkedin', 'https://linkedin.com'),
        ];

        $headerPages = Page::where('status', 'published')->orderBy('title')->get();

        return view('welcome', compact('banners', 'services', 'faqs', 'settings', 'headerPages'));
    }

    /**
     * Render a custom published page by slug.
     */
    public function showPage($slug)
    {
        $page = Page::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $settings = [
            'site_name'        => SiteSetting::getVal('site_name', 'SAEE Logistics'),
            'site_email'       => SiteSetting::getVal('site_email', 'info@saee.com.jo'),
            'site_phone'       => SiteSetting::getVal('site_phone', '+962 6 123 4567'),
            'site_address'     => SiteSetting::getVal('site_address', 'Amman, Jordan'),
            'meta_title'       => SiteSetting::getVal('meta_title', 'SAEE Logistics - Premier Delivery Solutions'),
            'meta_description' => SiteSetting::getVal('meta_description', 'SAEE is a premier delivery and logistics network connecting drivers and clients across the country.'),
            'social_facebook'  => SiteSetting::getVal('social_facebook', 'https://facebook.com'),
            'social_twitter'   => SiteSetting::getVal('social_twitter', 'https://twitter.com'),
            'social_instagram' => SiteSetting::getVal('social_instagram', 'https://instagram.com'),
            'social_linkedin'  => SiteSetting::getVal('social_linkedin', 'https://linkedin.com'),
        ];

        $headerPages = Page::where('status', 'published')->orderBy('title')->get();

        return view('public.page', compact('page', 'settings', 'headerPages'));
    }
}
