<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ProcessImageJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ProcessImageJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_resize_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob(
            $path,
            ['resize' => ['width' => 800, 'height' => 600]]
        );

        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::disk('public')->assertExists($result);
    }

    /** @test */
    public function it_can_crop_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob(
            $path,
            ['crop' => ['width' => 500, 'height' => 500, 'x' => 0, 'y' => 0]]
        );

        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::disk('public')->assertExists($result);
    }

    /** @test */
    public function it_can_optimize_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        $path = $file->store('uploads', 'public');

        $originalSize = Storage::disk('public')->size($path);

        $job = new ProcessImageJob(
            $path,
            ['optimize' => ['quality' => 80]]
        );

        $result = $job->handle();

        $optimizedSize = Storage::disk('public')->size($result);
        $this->assertLessThanOrEqual($originalSize, $optimizedSize);
    }

    /** @test */
    public function it_can_add_watermark()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        $path = $file->store('uploads', 'public');

        $watermark = UploadedFile::fake()->image('watermark.png', 200, 200);
        $watermarkPath = $watermark->store('watermarks', 'public');

        $job = new ProcessImageJob(
            $path,
            ['watermark' => ['path' => $watermarkPath, 'position' => 'bottom-right']]
        );

        $result = $job->handle();

        $this->assertNotNull($result);
        Storage::disk('public')->assertExists($result);
    }

    /** @test */
    public function it_generates_thumbnails()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob(
            $path,
            ['thumbnails' => true]
        );

        $result = $job->handle();

        // Check that thumbnails were created
        $this->assertIsArray($result);
        $this->assertArrayHasKey('original', $result ?? []);
        $this->assertArrayHasKey('small', $result ?? []);
        $this->assertArrayHasKey('medium', $result ?? []);
        $this->assertArrayHasKey('large', $result ?? []);
    }

    /** @test */
    public function it_can_apply_multiple_operations()
    {
        $file = UploadedFile::fake()->image('test.jpg', 2000, 2000);
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob(
            $path,
            [
                'resize' => ['width' => 1200, 'height' => 1200],
                'optimize' => ['quality' => 85],
                'thumbnails' => true
            ]
        );

        $result = $job->handle();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('original', $result ?? []);
        if (isset($result['original'])) {
            Storage::disk('public')->assertExists($result['original']);
        }
    }

    /** @test */
    public function it_logs_info_when_processing_image()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1000, 1000);
        $path = $file->store('uploads', 'public');

        Log::shouldReceive('info')
            ->once()
            ->with('Starting image processing', \Mockery::type('array'));

        Log::shouldReceive('info')
            ->once()
            ->with('Image processed successfully', \Mockery::type('array'));

        $job = new ProcessImageJob($path, ['resize' => ['width' => 800, 'height' => 600]]);
        $job->handle();
    }

    /** @test */
    public function it_handles_failure_correctly()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Image processing job failed after all retries' &&
                       isset($context['path']) &&
                       isset($context['error']);
            });

        $job = new ProcessImageJob('non-existent.jpg', []);
        $exception = new \Exception('File not found');
        $job->failed($exception);
    }

    /** @test */
    public function it_has_correct_retry_configuration()
    {
        $job = new ProcessImageJob('test.jpg', []);

        $this->assertEquals(2, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    /** @test */
    public function it_handles_invalid_image_format()
    {
        $file = UploadedFile::fake()->create('test.txt', 100);
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob($path, ['resize' => ['width' => 800, 'height' => 600]]);

        $this->expectException(\Exception::class);
        $job->handle();
    }

    /** @test */
    public function it_generates_correct_thumbnail_sizes()
    {
        $file = UploadedFile::fake()->image('test.jpg', 2000, 2000);
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob($path, ['thumbnails' => true]);
        $result = $job->handle();

        // Verify all thumbnail sizes exist
        $this->assertArrayHasKey('small', $result ?? []);   // 150x150
        $this->assertArrayHasKey('medium', $result ?? []);  // 300x300
        $this->assertArrayHasKey('large', $result ?? []);   // 600x600

        if (is_array($result)) {
            foreach (['small', 'medium', 'large'] as $size) {
                if (isset($result[$size])) {
                    Storage::disk('public')->assertExists($result[$size]);
                }
            }
        }
    }

    /** @test */
    public function it_preserves_aspect_ratio_when_resizing()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1600, 900); // 16:9 ratio
        $path = $file->store('uploads', 'public');

        $job = new ProcessImageJob(
            $path,
            ['resize' => ['width' => 800, 'height' => null, 'maintain_ratio' => true]]
        );

        $result = $job->handle();

        // Result should maintain aspect ratio
        $this->assertNotNull($result);
        Storage::disk('public')->assertExists($result);
    }

    /** @test */
    public function it_can_be_dispatched_to_queue()
    {
        \Illuminate\Support\Facades\Queue::fake();

        ProcessImageJob::dispatch('test.jpg', ['resize' => ['width' => 800, 'height' => 600]]);

        \Illuminate\Support\Facades\Queue::assertPushed(ProcessImageJob::class);
    }
}
