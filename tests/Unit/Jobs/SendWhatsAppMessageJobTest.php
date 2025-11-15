<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendWhatsAppMessageJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock WhatsApp configuration
        config([
            'services.whatsapp.token' => 'test_token_123',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.whatsapp.api_url' => 'https://graph.facebook.com/v17.0'
        ]);
    }

    /** @test */
    public function it_can_send_text_message_successfully()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['wa_id' => '1234567890']],
                'messages' => [['id' => 'wamid.123']]
            ], 200)
        ]);

        $job = new SendWhatsAppMessageJob(
            '+1234567890',
            'Hello, this is a test message',
            'text'
        );

        $job->handle();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'graph.facebook.com') &&
                   $request['messaging_product'] === 'whatsapp' &&
                   $request['type'] === 'text';
        });
    }

    /** @test */
    public function it_can_send_template_message()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['wa_id' => '1234567890']],
                'messages' => [['id' => 'wamid.456']]
            ], 200)
        ]);

        $job = new SendWhatsAppMessageJob(
            '+1234567890',
            'order_confirmation',
            'template',
            ['order_id' => '12345', 'grand_total' => '99.99']
        );

        $job->handle();

        Http::assertSent(function ($request) {
            return $request['type'] === 'template';
        });
    }

    /** @test */
    public function it_handles_rate_limiting()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'code' => 80007,
                    'message' => 'Rate limit exceeded'
                ]
            ], 429)
        ]);

        $job = new SendWhatsAppMessageJob(
            '+1234567890',
            'Test message',
            'text'
        );

        $this->expectException(\Exception::class);
        $job->handle();
    }

    /** @test */
    public function it_validates_phone_number_format()
    {
        Http::fake();

        // Valid formats
        $validNumbers = [
            '+1234567890',
            '+33612345678',
            '+221771234567'
        ];

        foreach ($validNumbers as $number) {
            $job = new SendWhatsAppMessageJob($number, 'Test', 'text');
            // Should not throw exception
            $this->assertInstanceOf(SendWhatsAppMessageJob::class, $job);
        }
    }

    /** @test */
    public function it_logs_info_when_sending_message()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messages' => [['id' => 'wamid.123']]
            ], 200)
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Sending WhatsApp message', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('WhatsApp message sent successfully', \Mockery::type('array'));

        $job = new SendWhatsAppMessageJob('+1234567890', 'Test', 'text');
        $job->handle();
    }

    /** @test */
    public function it_handles_failure_correctly()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'WhatsApp message job failed after all retries' &&
                       isset($context['to']) &&
                       isset($context['error']);
            });

        $job = new SendWhatsAppMessageJob('+1234567890', 'Test', 'text');
        $exception = new \Exception('WhatsApp API error');
        $job->failed($exception);
    }

    /** @test */
    public function it_has_correct_retry_configuration()
    {
        $job = new SendWhatsAppMessageJob('+1234567890', 'Test', 'text');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    /** @test */
    public function it_prepares_text_payload_correctly()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.123']]], 200)
        ]);

        $job = new SendWhatsAppMessageJob('+1234567890', 'Hello World', 'text');
        $job->handle();

        Http::assertSent(function ($request) {
            return $request['type'] === 'text' &&
                   $request['text']['body'] === 'Hello World';
        });
    }

    /** @test */
    public function it_prepares_template_payload_correctly()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.123']]], 200)
        ]);

        $variables = ['John', '12345'];
        $job = new SendWhatsAppMessageJob(
            '+1234567890',
            'welcome_template',
            'template',
            $variables
        );

        $job->handle();

        Http::assertSent(function ($request) {
            return $request['type'] === 'template' &&
                   isset($request['template']['name']);
        });
    }

    /** @test */
    public function it_handles_invalid_token_error()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'code' => 190,
                    'message' => 'Invalid OAuth access token'
                ]
            ], 401)
        ]);

        $job = new SendWhatsAppMessageJob('+1234567890', 'Test', 'text');

        $this->expectException(\Exception::class);
        $job->handle();
    }

    /** @test */
    public function it_can_be_dispatched_to_queue()
    {
        $this->expectsJobs(SendWhatsAppMessageJob::class);

        SendWhatsAppMessageJob::dispatch('+1234567890', 'Queued message', 'text');
    }
}
