<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
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
     * WhatsApp message data
     */
    protected $phoneNumber;
    protected $message;
    protected $messageType;
    protected $additionalData;

    /**
     * Create a new job instance.
     *
     * @param string $phoneNumber Recipient phone number (with country code)
     * @param string $message Message content
     * @param string $messageType Message type (text, template, etc.)
     * @param array $additionalData Additional data (template params, media, etc.)
     */
    public function __construct(
        string $phoneNumber,
        string $message,
        string $messageType = 'text',
        array $additionalData = []
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->messageType = $messageType;
        $this->additionalData = $additionalData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if WhatsApp is enabled
        if (!config('services.whatsapp.enabled', false)) {
            Log::info('WhatsApp messaging is disabled, skipping message', [
                'phone' => $this->phoneNumber
            ]);
            return;
        }

        try {
            $apiUrl = config('services.whatsapp.api_url');
            $phoneNumberId = config('services.whatsapp.phone_number_id');
            $accessToken = config('services.whatsapp.api_token');

            // Prepare message payload based on type
            $payload = $this->preparePayload();

            // Send message via WhatsApp Business API
            $response = Http::withToken($accessToken)
                ->post("{$apiUrl}/{$phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $this->phoneNumber,
                    'message_id' => $response->json('messages.0.id')
                ]);
            } else {
                throw new \Exception('WhatsApp API returned error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'phone' => $this->phoneNumber,
                'error' => $e->getMessage()
            ]);

            // Re-throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * Prepare the message payload based on message type
     *
     * @return array
     */
    protected function preparePayload(): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->phoneNumber,
        ];

        switch ($this->messageType) {
            case 'template':
                $payload['type'] = 'template';
                $payload['template'] = $this->additionalData['template'] ?? [];
                break;

            case 'text':
            default:
                $payload['type'] = 'text';
                $payload['text'] = [
                    'preview_url' => false,
                    'body' => $this->message
                ];
                break;
        }

        return $payload;
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('WhatsApp message job failed after all retries', [
            'phone' => $this->phoneNumber,
            'error' => $exception->getMessage()
        ]);
    }
}
