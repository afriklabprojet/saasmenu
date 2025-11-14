<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Image processing data
     */
    protected $imagePath;
    protected $operations;
    protected $disk;

    /**
     * Create a new job instance.
     *
     * @param string $imagePath Path to the image file
     * @param array $operations Operations to perform (resize, crop, optimize, etc.)
     * @param string $disk Storage disk to use
     */
    public function __construct(string $imagePath, array $operations = [], string $disk = 'public')
    {
        $this->imagePath = $imagePath;
        $this->operations = $operations;
        $this->disk = $disk;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Check if image exists
            if (!Storage::disk($this->disk)->exists($this->imagePath)) {
                throw new \Exception("Image not found: {$this->imagePath}");
            }

            // Get full path
            $fullPath = Storage::disk($this->disk)->path($this->imagePath);

            // Load image
            $image = Image::make($fullPath);

            // Apply operations
            foreach ($this->operations as $operation => $params) {
                $this->applyOperation($image, $operation, $params);
            }

            // Save processed image
            $image->save($fullPath);

            Log::info('Image processed successfully', [
                'path' => $this->imagePath,
                'operations' => array_keys($this->operations)
            ]);

            // Generate thumbnails if requested
            if (isset($this->operations['thumbnails'])) {
                $this->generateThumbnails($fullPath, $this->operations['thumbnails']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process image', [
                'path' => $this->imagePath,
                'error' => $e->getMessage()
            ]);

            // Re-throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * Apply a single operation to the image
     *
     * @param \Intervention\Image\Image $image
     * @param string $operation
     * @param mixed $params
     * @return void
     */
    protected function applyOperation($image, string $operation, $params)
    {
        switch ($operation) {
            case 'resize':
                $width = $params['width'] ?? null;
                $height = $params['height'] ?? null;
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                break;

            case 'crop':
                $width = $params['width'];
                $height = $params['height'];
                $x = $params['x'] ?? null;
                $y = $params['y'] ?? null;
                $image->crop($width, $height, $x, $y);
                break;

            case 'fit':
                $width = $params['width'];
                $height = $params['height'];
                $position = $params['position'] ?? 'center';
                $image->fit($width, $height, null, $position);
                break;

            case 'optimize':
                $quality = $params['quality'] ?? 80;
                $image->encode(null, $quality);
                break;

            case 'watermark':
                $watermarkPath = $params['path'];
                $position = $params['position'] ?? 'bottom-right';
                if (file_exists($watermarkPath)) {
                    $image->insert($watermarkPath, $position, 10, 10);
                }
                break;
        }
    }

    /**
     * Generate thumbnails
     *
     * @param string $originalPath
     * @param array $sizes
     * @return void
     */
    protected function generateThumbnails(string $originalPath, array $sizes)
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        foreach ($sizes as $size) {
            $width = $size['width'];
            $height = $size['height'];
            $suffix = $size['suffix'] ?? "{$width}x{$height}";

            $thumbnailPath = "{$directory}/{$filename}_{$suffix}.{$extension}";

            $thumbnail = Image::make($originalPath);
            $thumbnail->fit($width, $height);
            $thumbnail->save($thumbnailPath);

            Log::info('Thumbnail generated', [
                'path' => $thumbnailPath,
                'size' => "{$width}x{$height}"
            ]);
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
        Log::error('Image processing job failed after all retries', [
            'path' => $this->imagePath,
            'error' => $exception->getMessage()
        ]);
    }
}
