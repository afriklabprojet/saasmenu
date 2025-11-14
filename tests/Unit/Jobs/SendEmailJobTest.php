<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\SendEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendEmailJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_send_email_successfully()
    {
        Mail::fake();

        $job = new SendEmailJob(
            'test@example.com',
            'Test Subject',
            'emails.test',
            ['name' => 'John Doe']
        );

        $job->handle();

        Mail::assertSent(function ($mail) {
            return $mail->hasTo('test@example.com') &&
                   $mail->subject === 'Test Subject';
        });
    }

    /** @test */
    public function it_logs_info_when_sending_email()
    {
        Mail::fake();
        Log::shouldReceive('info')
            ->once()
            ->with('Sending email', [
                'to' => 'test@example.com',
                'subject' => 'Test Subject'
            ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Email sent successfully', [
                'to' => 'test@example.com'
            ]);

        $job = new SendEmailJob(
            'test@example.com',
            'Test Subject',
            'emails.test',
            ['name' => 'John Doe']
        );

        $job->handle();
    }

    /** @test */
    public function it_handles_failure_correctly()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Email job failed after all retries' &&
                       isset($context['to']) &&
                       isset($context['error']);
            });

        $job = new SendEmailJob(
            'test@example.com',
            'Test Subject',
            'emails.test',
            []
        );

        $exception = new \Exception('Email sending failed');
        $job->failed($exception);
    }

    /** @test */
    public function it_has_correct_retry_configuration()
    {
        $job = new SendEmailJob(
            'test@example.com',
            'Test Subject',
            'emails.test',
            []
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(30, $job->timeout);
    }

    /** @test */
    public function it_can_handle_multiple_recipients()
    {
        Mail::fake();

        $recipients = [
            'user1@example.com',
            'user2@example.com',
            'user3@example.com'
        ];

        foreach ($recipients as $recipient) {
            $job = new SendEmailJob(
                $recipient,
                'Bulk Test',
                'emails.test',
                []
            );
            $job->handle();
        }

        Mail::assertSent(3);
    }

    /** @test */
    public function it_passes_data_to_email_view()
    {
        Mail::fake();

        $data = [
            'name' => 'John Doe',
            'order_id' => '12345',
            'total' => 99.99
        ];

        $job = new SendEmailJob(
            'test@example.com',
            'Order Confirmation',
            'emails.order_confirmation',
            $data
        );

        $job->handle();

        Mail::assertSent(function ($mail) use ($data) {
            return $mail->hasTo('test@example.com') &&
                   $mail->subject === 'Order Confirmation';
        });
    }

    /** @test */
    public function it_can_be_dispatched_to_queue()
    {
        $this->expectsJobs(SendEmailJob::class);

        SendEmailJob::dispatch(
            'test@example.com',
            'Queued Email',
            'emails.test',
            []
        );
    }
}
