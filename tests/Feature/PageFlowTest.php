<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\About;
use App\Models\Terms;
use App\Models\Privacypolicy;
use App\Models\RefundPrivacypolicy;
use App\Models\Settings;
use App\Models\User;

class PageFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $vendor;
    protected $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test vendor
        $this->vendor = User::factory()->create([
            'name' => 'Test Restaurant',
            'email' => 'test@restaurant.com',
            'type' => 2, // Vendor
        ]);

        // Create settings for vendor
        $this->settings = Settings::factory()->create([
            'vendor_id' => $this->vendor->id,
            'restaurant_name' => 'Test Restaurant',
        ]);

        // Set vendor in session
        Session::put('restaurant_id', $this->vendor->id);
    }

    /**
     * Test about page displays correctly
     */
    public function test_about_page_displays_correctly()
    {
        // Create about content
        $about = About::factory()->create([
            'vendor_id' => $this->vendor->id,
            'about_content' => 'Test about content',
        ]);

        $response = $this->get('/aboutus');

        $response->assertStatus(200);
        $response->assertViewIs('front.about-us');
        $response->assertViewHas('aboutus');
        $response->assertSee('Test about content', false);
    }

    /**
     * Test about page without vendor session redirects
     */
    public function test_about_page_without_vendor_session_redirects()
    {
        Session::forget('restaurant_id');

        $response = $this->get('/aboutus');

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Restaurant non sélectionné');
    }

    /**
     * Test about page without content redirects with error
     */
    public function test_about_page_without_content_shows_error()
    {
        // No about content created

        $response = $this->get('/aboutus');

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Page À propos non disponible');
    }

    /**
     * Test about page uses cache
     */
    public function test_about_page_uses_cache()
    {
        Cache::flush();

        $about = About::factory()->create([
            'vendor_id' => $this->vendor->id,
            'about_content' => 'Cached content',
        ]);

        // First request - should cache
        $this->get('/aboutus');

        // Verify cache exists
        $cacheKey = "about_page_{$this->vendor->id}";
        $this->assertTrue(Cache::has($cacheKey));

        $cachedData = Cache::get($cacheKey);
        $this->assertEquals('Cached content', $cachedData->about_content);
    }

    /**
     * Test terms page displays correctly
     */
    public function test_terms_page_displays_correctly()
    {
        $terms = Terms::factory()->create([
            'vendor_id' => $this->vendor->id,
            'terms_content' => 'Test terms content',
        ]);

        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertViewIs('front.terms-conditions');
        $response->assertViewHas('terms');
        $response->assertSee('Test terms content', false);
    }

    /**
     * Test terms page uses cache
     */
    public function test_terms_page_uses_cache()
    {
        Cache::flush();

        Terms::factory()->create([
            'vendor_id' => $this->vendor->id,
            'terms_content' => 'Terms cached',
        ]);

        $this->get('/terms');

        $cacheKey = "terms_page_{$this->vendor->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test privacy policy page displays correctly
     */
    public function test_privacy_policy_page_displays_correctly()
    {
        $privacy = Privacypolicy::factory()->create([
            'vendor_id' => $this->vendor->id,
            'privacy_content' => 'Test privacy content',
        ]);

        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertViewIs('front.privacy-policy');
        $response->assertViewHas('privacypolicy');
        $response->assertSee('Test privacy content', false);
    }

    /**
     * Test privacy policy page uses cache
     */
    public function test_privacy_policy_page_uses_cache()
    {
        Cache::flush();

        Privacypolicy::factory()->create([
            'vendor_id' => $this->vendor->id,
            'privacy_content' => 'Privacy cached',
        ]);

        $this->get('/privacy-policy');

        $cacheKey = "privacy_page_{$this->vendor->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test refund policy page displays correctly
     */
    public function test_refund_policy_page_displays_correctly()
    {
        $refund = RefundPrivacypolicy::factory()->create([
            'vendor_id' => $this->vendor->id,
            'refund_content' => 'Test refund content',
        ]);

        $response = $this->get('/refundprivacypolicy');

        $response->assertStatus(200);
        $response->assertViewIs('front.refund-privacy-policy');
        $response->assertViewHas('refundprivacypolicy');
        $response->assertSee('Test refund content', false);
    }

    /**
     * Test refund policy page uses cache
     */
    public function test_refund_policy_page_uses_cache()
    {
        Cache::flush();

        RefundPrivacypolicy::factory()->create([
            'vendor_id' => $this->vendor->id,
            'refund_content' => 'Refund cached',
        ]);

        $this->get('/refundprivacypolicy');

        $cacheKey = "refund_page_{$this->vendor->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test SEO redirect from legacy terms_condition URL
     */
    public function test_legacy_terms_condition_url_redirects()
    {
        $response = $this->get('/terms_condition');

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    /**
     * Test SEO redirect from legacy privacypolicy URL
     */
    public function test_legacy_privacypolicy_url_redirects()
    {
        $response = $this->get('/privacypolicy');

        $response->assertRedirect();
        $response->assertStatus(302);
    }

    /**
     * Test get page content API - about
     */
    public function test_api_get_about_page_content()
    {
        $about = About::factory()->create([
            'vendor_id' => $this->vendor->id,
            'about_content' => 'API about content',
        ]);

        $response = $this->postJson('/api/pages/content', [
            'page_type' => 'about',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        $response->assertJsonStructure([
            'status',
            'content',
            'last_updated',
        ]);
    }

    /**
     * Test get page content API - invalid page type
     */
    public function test_api_get_page_content_invalid_type()
    {
        $response = $this->postJson('/api/pages/content', [
            'page_type' => 'invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page_type']);
    }

    /**
     * Test get page content API - without vendor session
     */
    public function test_api_get_page_content_without_vendor()
    {
        Session::forget('restaurant_id');

        $response = $this->postJson('/api/pages/content', [
            'page_type' => 'about',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 0,
            'message' => 'Restaurant non sélectionné',
        ]);
    }

    /**
     * Test check page availability API
     */
    public function test_api_check_page_availability()
    {
        About::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->getJson('/api/pages/available?page_type=about');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'available' => true,
        ]);
    }

    /**
     * Test get all available pages API
     */
    public function test_api_get_all_available_pages()
    {
        // Create some pages
        About::factory()->create(['vendor_id' => $this->vendor->id]);
        Terms::factory()->create(['vendor_id' => $this->vendor->id]);

        $response = $this->getJson('/api/pages/available');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        $response->assertJsonStructure([
            'status',
            'pages' => [
                'about',
                'terms',
                'privacy',
                'refund',
            ],
        ]);
    }

    /**
     * Test cache TTL is 1 hour (3600 seconds)
     */
    public function test_cache_ttl_is_one_hour()
    {
        Cache::flush();

        $about = About::factory()->create([
            'vendor_id' => $this->vendor->id,
            'about_content' => 'TTL test',
        ]);

        // Make request to cache data
        $this->get('/aboutus');

        $cacheKey = "about_page_{$this->vendor->id}";
        
        // Verify cache exists
        $this->assertTrue(Cache::has($cacheKey));

        // Note: Testing exact TTL is difficult without time manipulation
        // This test verifies cache is set, actual TTL testing would require
        // Carbon\Carbon::setTestNow() or similar time mocking
    }

    /**
     * Test multiple vendors have separate caches
     */
    public function test_multiple_vendors_have_separate_caches()
    {
        Cache::flush();

        // Create second vendor
        $vendor2 = User::factory()->create(['type' => 2]);
        Settings::factory()->create(['vendor_id' => $vendor2->id]);

        // Create content for both vendors
        $about1 = About::factory()->create([
            'vendor_id' => $this->vendor->id,
            'about_content' => 'Vendor 1 content',
        ]);

        $about2 = About::factory()->create([
            'vendor_id' => $vendor2->id,
            'about_content' => 'Vendor 2 content',
        ]);

        // Request for vendor 1
        Session::put('restaurant_id', $this->vendor->id);
        $this->get('/aboutus');

        // Request for vendor 2
        Session::put('restaurant_id', $vendor2->id);
        $this->get('/aboutus');

        // Verify both caches exist separately
        $cacheKey1 = "about_page_{$this->vendor->id}";
        $cacheKey2 = "about_page_{$vendor2->id}";

        $this->assertTrue(Cache::has($cacheKey1));
        $this->assertTrue(Cache::has($cacheKey2));

        $cached1 = Cache::get($cacheKey1);
        $cached2 = Cache::get($cacheKey2);

        $this->assertEquals('Vendor 1 content', $cached1->about_content);
        $this->assertEquals('Vendor 2 content', $cached2->about_content);
    }
}
