<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Contact;
use App\Models\Subscriber;
use App\Models\TableBook;
use App\Models\Settings;
use App\Models\User;
use App\Models\Timing;

class ContactFlowTest extends TestCase
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
            'email' => 'vendor@restaurant.com',
            'type' => 2, // Vendor
        ]);

        // Create settings for vendor
        $this->settings = Settings::factory()->create([
            'vendor_id' => $this->vendor->id,
            'restaurant_name' => 'Test Restaurant',
            'recaptcha_secret_key' => 'test_secret_key',
        ]);

        // Set vendor in session
        Session::put('restaurant_id', $this->vendor->id);

        // Mock reCAPTCHA verification
        Http::fake([
            'https://www.google.com/recaptcha/*' => Http::response([
                'success' => true,
                'score' => 0.9,
            ], 200),
        ]);
    }

    /**
     * Test contact page displays correctly
     */
    public function test_contact_page_displays()
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertViewIs('front.contact');
        $response->assertViewHas('settings');
    }

    /**
     * Test contact form submission success
     */
    public function test_contact_form_submission_success()
    {
        Mail::fake();

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success', 'Votre message a été envoyé avec succès');

        // Verify contact saved
        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'vendor_id' => $this->vendor->id,
        ]);

        // Verify audit log created
        $this->assertDatabaseHas('audits', [
            'vendor_id' => $this->vendor->id,
            'action' => 'Contact soumis',
        ]);
    }

    /**
     * Test contact form validation errors - missing name
     */
    public function test_contact_form_validation_missing_name()
    {
        $contactData = [
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertSessionHasErrors(['name']);
        $response->assertRedirect();
    }

    /**
     * Test contact form validation errors - invalid email
     */
    public function test_contact_form_validation_invalid_email()
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'mobile' => '1234567890',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test contact form validation errors - missing mobile
     */
    public function test_contact_form_validation_missing_mobile()
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertSessionHasErrors(['mobile']);
    }

    /**
     * Test contact form validation errors - message too short
     */
    public function test_contact_form_validation_message_too_short()
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => 'Hi',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertSessionHasErrors(['message']);
    }

    /**
     * Test contact form XSS protection strips tags
     */
    public function test_contact_form_xss_protection()
    {
        Mail::fake();

        $contactData = [
            'name' => '<script>alert("XSS")</script>John',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => '<b>Bold message</b> with <script>alert("XSS")</script>',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/contact');

        // Verify tags stripped
        $contact = Contact::where('email', 'john@example.com')->first();
        $this->assertEquals('John', $contact->name);
        $this->assertEquals('Bold message with', $contact->message);
        $this->assertStringNotContainsString('<script>', $contact->message);
        $this->assertStringNotContainsString('<b>', $contact->message);
    }

    /**
     * Test contact form fails with low reCAPTCHA score
     */
    public function test_contact_form_fails_with_low_recaptcha_score()
    {
        Http::fake([
            'https://www.google.com/recaptcha/*' => Http::response([
                'success' => true,
                'score' => 0.3, // Below threshold of 0.5
            ], 200),
        ]);

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('error', 'La vérification reCAPTCHA a échoué');

        // Verify contact NOT saved
        $this->assertDatabaseMissing('contacts', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test contact email notification sent to vendor
     */
    public function test_contact_email_notification_sent()
    {
        Mail::fake();
        Log::spy();

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/contact');

        // Verify log entry created for email notification
        Log::shouldHaveReceived('info')
            ->once()
            ->with('Contact email notification sent', \Mockery::type('array'));
    }

    /**
     * Test newsletter subscription success
     */
    public function test_newsletter_subscription_success()
    {
        $subscribeData = [
            'email' => 'subscriber@example.com',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/subscribe', $subscribeData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Merci pour votre abonnement !');

        // Verify subscriber saved
        $this->assertDatabaseHas('subscribers', [
            'email' => 'subscriber@example.com',
            'vendor_id' => $this->vendor->id,
        ]);

        // Verify audit log
        $this->assertDatabaseHas('audits', [
            'vendor_id' => $this->vendor->id,
            'action' => 'Abonnement newsletter',
        ]);
    }

    /**
     * Test newsletter duplicate email prevention
     */
    public function test_newsletter_duplicate_prevention()
    {
        // Create existing subscriber
        Subscriber::factory()->create([
            'email' => 'existing@example.com',
            'vendor_id' => $this->vendor->id,
        ]);

        $subscribeData = [
            'email' => 'existing@example.com',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/subscribe', $subscribeData);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cet email est déjà abonné');

        // Verify only one subscriber exists
        $this->assertEquals(1, Subscriber::where('email', 'existing@example.com')->count());
    }

    /**
     * Test newsletter validation - invalid email
     */
    public function test_newsletter_validation_invalid_email()
    {
        $subscribeData = [
            'email' => 'not-an-email',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/subscribe', $subscribeData);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test table booking page displays
     */
    public function test_table_booking_page_displays()
    {
        $response = $this->get('/book');

        $response->assertStatus(200);
        $response->assertViewIs('front.table-booking');
        $response->assertViewHas('settings');
    }

    /**
     * Test table booking submission success
     */
    public function test_table_booking_submission_success()
    {
        Mail::fake();

        $bookingData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'mobile' => '9876543210',
            'guest_counts' => 4,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '19:00',
            'message' => 'Special occasion',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/book/save', $bookingData);

        $response->assertRedirect('/book');
        $response->assertSessionHas('success', 'Votre réservation a été enregistrée avec succès');

        // Verify booking saved
        $this->assertDatabaseHas('table_books', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'mobile' => '9876543210',
            'guest_counts' => 4,
            'vendor_id' => $this->vendor->id,
        ]);

        // Verify audit log
        $this->assertDatabaseHas('audits', [
            'vendor_id' => $this->vendor->id,
            'action' => 'Réservation table',
        ]);
    }

    /**
     * Test table booking validation - missing name
     */
    public function test_table_booking_validation_missing_name()
    {
        $bookingData = [
            'email' => 'jane@example.com',
            'mobile' => '9876543210',
            'guest_counts' => 4,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '19:00',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/book/save', $bookingData);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test table booking validation - invalid guest count
     */
    public function test_table_booking_validation_invalid_guest_count()
    {
        $bookingData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'mobile' => '9876543210',
            'guest_counts' => 0,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '19:00',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/book/save', $bookingData);

        $response->assertSessionHasErrors(['guest_counts']);
    }

    /**
     * Test table booking validation - past date
     */
    public function test_table_booking_validation_past_date()
    {
        $bookingData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'mobile' => '9876543210',
            'guest_counts' => 4,
            'booking_date' => now()->subDays(1)->format('Y-m-d'),
            'booking_time' => '19:00',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/book/save', $bookingData);

        $response->assertSessionHasErrors(['booking_date']);
    }

    /**
     * Test table booking slot availability - max 5 bookings
     */
    public function test_table_booking_slot_availability_max_bookings()
    {
        Mail::fake();

        $bookingDate = now()->addDays(2)->format('Y-m-d');
        $bookingTime = '19:00';

        // Create 5 existing bookings for the same slot
        TableBook::factory()->count(5)->create([
            'vendor_id' => $this->vendor->id,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'status' => 1, // Active
        ]);

        // Try to book 6th slot
        $bookingData = [
            'name' => 'Late Booker',
            'email' => 'late@example.com',
            'mobile' => '1111111111',
            'guest_counts' => 2,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/book/save', $bookingData);

        $response->assertRedirect('/book');
        $response->assertSessionHas('error', 'Ce créneau horaire est complet. Veuillez choisir un autre horaire.');

        // Verify booking NOT saved
        $this->assertDatabaseMissing('table_books', [
            'email' => 'late@example.com',
        ]);
    }

    /**
     * Test get available time slots API
     */
    public function test_get_available_time_slots_api()
    {
        $response = $this->getJson('/api/booking/timeslots?date=' . now()->addDays(1)->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        $response->assertJsonStructure([
            'status',
            'slots' => [
                '*' => [
                    'time',
                    'label',
                    'available',
                    'remaining_capacity',
                ],
            ],
        ]);

        // Verify slots are between 12:00 and 22:00
        $slots = $response->json('slots');
        $this->assertNotEmpty($slots);
        
        foreach ($slots as $slot) {
            $hour = (int) substr($slot['time'], 0, 2);
            $this->assertGreaterThanOrEqual(12, $hour);
            $this->assertLessThan(22, $hour);
        }
    }

    /**
     * Test get time slots API without date parameter
     */
    public function test_get_time_slots_api_without_date()
    {
        $response = $this->getJson('/api/booking/timeslots');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    /**
     * Test get time slots API with past date
     */
    public function test_get_time_slots_api_with_past_date()
    {
        $response = $this->getJson('/api/booking/timeslots?date=' . now()->subDays(1)->format('Y-m-d'));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    /**
     * Test get time slots shows reduced availability
     */
    public function test_get_time_slots_shows_reduced_availability()
    {
        $testDate = now()->addDays(1)->format('Y-m-d');
        $testTime = '19:00';

        // Create 3 bookings for specific slot
        TableBook::factory()->count(3)->create([
            'vendor_id' => $this->vendor->id,
            'booking_date' => $testDate,
            'booking_time' => $testTime,
            'status' => 1,
        ]);

        $response = $this->getJson("/api/booking/timeslots?date={$testDate}");

        $response->assertStatus(200);

        $slots = $response->json('slots');
        $slot19 = collect($slots)->firstWhere('time', '19:00');

        $this->assertNotNull($slot19);
        $this->assertTrue($slot19['available']);
        $this->assertEquals(2, $slot19['remaining_capacity']); // 5 - 3 = 2
    }

    /**
     * Test reCAPTCHA verification method
     */
    public function test_recaptcha_verification_success()
    {
        Http::fake([
            'https://www.google.com/recaptcha/*' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'message' => 'Testing reCAPTCHA',
            'recaptcha_token' => 'valid_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');
    }

    /**
     * Test reCAPTCHA verification failure
     */
    public function test_recaptcha_verification_failure()
    {
        Http::fake([
            'https://www.google.com/recaptcha/*' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '1234567890',
            'message' => 'Testing reCAPTCHA failure',
            'recaptcha_token' => 'invalid_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('error');
    }

    /**
     * Test contact without vendor session
     */
    public function test_contact_without_vendor_session()
    {
        Session::forget('restaurant_id');

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '1234567890',
            'message' => 'Test message',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/contact/save', $contactData);

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Restaurant non sélectionné');
    }

    /**
     * Test booking without vendor session
     */
    public function test_booking_without_vendor_session()
    {
        Session::forget('restaurant_id');

        $bookingData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'mobile' => '9876543210',
            'guest_counts' => 4,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '19:00',
            'recaptcha_token' => 'test_token',
        ];

        $response = $this->post('/book/save', $bookingData);

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }
}
