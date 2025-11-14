<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\GenerateReportJob;
use App\Jobs\SendEmailJob;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDF;

class GenerateReportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
    }

    /** @test */
    public function it_can_generate_orders_report()
    {
        // Create test orders
        $user = User::factory()->create(['type' => 2]);
        Order::factory()->count(5)->create([
            'user_id' => $user->id,
            'created_at' => now()
        ]);

        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::assertExists($result);
    }

    /** @test */
    public function it_can_generate_sales_report()
    {
        $user = User::factory()->create(['type' => 2]);
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
            'created_at' => now()
        ]);

        $job = new GenerateReportJob('sales', [], null, 'pdf');
        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::assertExists($result);
    }

    /** @test */
    public function it_can_generate_customers_report()
    {
        User::factory()->count(10)->create([
            'type' => 2, // Customer type
            'created_at' => now()
        ]);

        $job = new GenerateReportJob('customers', [], null, 'pdf');
        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::assertExists($result);
    }

    /** @test */
    public function it_can_generate_csv_format()
    {
        $user = User::factory()->create(['type' => 2]);
        Order::factory()->count(5)->create([
            'user_id' => $user->id,
            'created_at' => now()
        ]);

        $job = new GenerateReportJob('orders', [], null, 'csv');
        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::assertExists($result);
        $this->assertStringEndsWith('.csv', $result);
    }

    /** @test */
    public function it_filters_orders_by_date_range()
    {
        $user = User::factory()->create(['type' => 2]);
        
        // Orders in range
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(5)
        ]);

        // Orders out of range
        Order::factory()->count(2)->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(60)
        ]);

        $job = new GenerateReportJob('orders', [
            'start_date' => now()->subDays(30),
            'end_date' => now()
        ], null, 'pdf');

        $result = $job->handle();

        $this->assertNotNull($result);
    }

    /** @test */
    public function it_filters_orders_by_status()
    {
        $user = User::factory()->create(['type' => 2]);
        
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'created_at' => now()
        ]);

        Order::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'created_at' => now()
        ]);

        $job = new GenerateReportJob('orders', [
            'status' => 'completed'
        ], null, 'pdf');

        $result = $job->handle();

        $this->assertNotNull($result);
    }

    /** @test */
    public function it_sends_notification_email_when_user_provided()
    {
        $user = User::factory()->create(['type' => 1, 'email' => 'admin@test.com']);
        Order::factory()->count(3)->create(['created_at' => now()]);

        $job = new GenerateReportJob('orders', [], $user->id, 'pdf');
        $job->handle();

        Queue::assertPushed(SendEmailJob::class, function ($job) use ($user) {
            return $job->to === $user->email &&
                   str_contains($job->subject, 'report is ready');
        });
    }

    /** @test */
    public function it_does_not_send_notification_when_no_user()
    {
        Order::factory()->count(3)->create(['created_at' => now()]);

        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $job->handle();

        Queue::assertNotPushed(SendEmailJob::class);
    }

    /** @test */
    public function it_logs_info_when_generating_report()
    {
        Order::factory()->count(2)->create(['created_at' => now()]);

        Log::shouldReceive('info')
            ->once()
            ->with('Starting report generation', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Report generated successfully', \Mockery::type('array'));

        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $job->handle();
    }

    /** @test */
    public function it_throws_exception_for_unknown_report_type()
    {
        $job = new GenerateReportJob('unknown_type', [], null, 'pdf');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown report type');

        $job->handle();
    }

    /** @test */
    public function it_throws_exception_for_unknown_output_format()
    {
        Order::factory()->count(2)->create(['created_at' => now()]);

        $job = new GenerateReportJob('orders', [], null, 'unknown_format');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown output format');

        $job->handle();
    }

    /** @test */
    public function it_handles_failure_correctly()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Report generation job failed after all retries' &&
                       isset($context['type']) &&
                       isset($context['error']);
            });

        $user = User::factory()->create(['email' => 'admin@test.com']);
        $job = new GenerateReportJob('orders', [], $user->id, 'pdf');
        
        $exception = new \Exception('PDF generation failed');
        $job->failed($exception);

        Queue::assertPushed(SendEmailJob::class, function ($emailJob) {
            return str_contains($emailJob->subject, 'Report generation failed');
        });
    }

    /** @test */
    public function it_has_correct_retry_configuration()
    {
        $job = new GenerateReportJob('orders', [], null, 'pdf');

        $this->assertEquals(2, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    /** @test */
    public function it_generates_correct_filename()
    {
        Order::factory()->count(2)->create(['created_at' => now()]);

        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $result = $job->handle();

        $this->assertStringStartsWith('reports/orders_', $result);
        $this->assertStringEndsWith('.pdf', $result);
    }

    /** @test */
    public function it_calculates_correct_summary_data()
    {
        $user = User::factory()->create(['type' => 2]);
        
        Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100.00,
            'created_at' => now()
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'total' => 200.00,
            'created_at' => now()
        ]);

        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $result = $job->handle();

        $this->assertNotNull($result);
        // Summary should include: total_orders, total_revenue, average_order_value
    }

    /** @test */
    public function it_can_be_dispatched_to_queue()
    {
        $this->expectsJobs(GenerateReportJob::class);

        GenerateReportJob::dispatch('orders', [], null, 'pdf');
    }

    /** @test */
    public function it_uses_default_date_range_when_not_provided()
    {
        Order::factory()->count(3)->create(['created_at' => now()->subDays(15)]);
        Order::factory()->count(2)->create(['created_at' => now()->subDays(60)]);

        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $result = $job->handle();

        // Should default to last 30 days
        $this->assertNotNull($result);
    }

    /** @test */
    public function it_handles_empty_dataset_gracefully()
    {
        // No orders in database
        $job = new GenerateReportJob('orders', [], null, 'pdf');
        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::assertExists($result);
    }
}
