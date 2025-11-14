<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Email data
     */
    protected $emailData;
    protected $template;
    protected $to;
    protected $subject;

    /**
     * Create a new job instance.
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $template Email template view name
     * @param array $emailData Data to pass to the email template
     */
    public function __construct(string $to, string $subject, string $template, array $emailData = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->template = $template;
        $this->emailData = $emailData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::send($this->template, $this->emailData, function ($message) {
                $message->to($this->to)
                    ->subject($this->subject);
            });

            Log::info('Email sent successfully', [
                'to' => $this->to,
                'subject' => $this->subject,
                'template' => $this->template
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'to' => $this->to,
                'subject' => $this->subject,
                'error' => $e->getMessage()
            ]);

            // Re-throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Email job failed after all retries', [
            'to' => $this->to,
            'subject' => $this->subject,
            'error' => $exception->getMessage()
        ]);
    }
}
