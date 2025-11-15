<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class GenerateReportJob implements ShouldQueue
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
    public $timeout = 120;

    /**
     * Report data
     */
    protected $reportType;
    protected $reportParams;
    protected $userId;
    protected $outputFormat;

    /**
     * Create a new job instance.
     *
     * @param string $reportType Type of report (orders, sales, inventory, etc.)
     * @param array $reportParams Report parameters (date range, filters, etc.)
     * @param int|null $userId User requesting the report
     * @param string $outputFormat Output format (pdf, excel, csv)
     */
    public function __construct(
        string $reportType,
        array $reportParams = [],
        ?int $userId = null,
        string $outputFormat = 'pdf'
    ) {
        $this->reportType = $reportType;
        $this->reportParams = $reportParams;
        $this->userId = $userId;
        $this->outputFormat = $outputFormat;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Starting report generation', [
                'type' => $this->reportType,
                'user_id' => $this->userId,
                'format' => $this->outputFormat
            ]);

            // Generate report based on type
            $reportData = $this->generateReportData();

            // Generate file based on format
            $filePath = $this->generateFile($reportData);

            // Notify user (if provided)
            if ($this->userId) {
                $this->notifyUser($filePath);
            }

            Log::info('Report generated successfully', [
                'type' => $this->reportType,
                'file' => $filePath
            ]);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'type' => $this->reportType,
                'error' => $e->getMessage()
            ]);

            // Re-throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * Generate report data based on report type
     *
     * @return array
     */
    protected function generateReportData(): array
    {
        switch ($this->reportType) {
            case 'orders':
                return $this->generateOrdersReport();

            case 'sales':
                return $this->generateSalesReport();

            case 'inventory':
                return $this->generateInventoryReport();

            case 'customers':
                return $this->generateCustomersReport();

            default:
                throw new \Exception("Unknown report type: {$this->reportType}");
        }
    }

    /**
     * Generate orders report
     *
     * @return array
     */
    protected function generateOrdersReport(): array
    {
        $startDate = $this->reportParams['start_date'] ?? now()->subDays(30);
        $endDate = $this->reportParams['end_date'] ?? now();
        $status = $this->reportParams['status'] ?? null;

        $query = Order::whereBetween('created_at', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->with(['user', 'orderitems'])->get();

        return [
            'title' => 'Orders Report',
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $orders,
            'summary' => [
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total'),
                'average_order_value' => $orders->avg('total'),
            ]
        ];
    }

    /**
     * Generate sales report
     *
     * @return array
     */
    protected function generateSalesReport(): array
    {
        $startDate = $this->reportParams['start_date'] ?? now()->subDays(30);
        $endDate = $this->reportParams['end_date'] ?? now();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->get();

        return [
            'title' => 'Sales Report',
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $orders,
            'summary' => [
                'total_sales' => $orders->sum('total'),
                'total_orders' => $orders->count(),
                'total_tax' => $orders->sum('tax'),
                'total_delivery' => $orders->sum('delivery_fee'),
            ]
        ];
    }

    /**
     * Generate inventory report
     *
     * @return array
     */
    protected function generateInventoryReport(): array
    {
        // Placeholder - implement based on your inventory model
        return [
            'title' => 'Inventory Report',
            'data' => [],
            'summary' => []
        ];
    }

    /**
     * Generate customers report
     *
     * @return array
     */
    protected function generateCustomersReport(): array
    {
        $startDate = $this->reportParams['start_date'] ?? now()->subDays(30);
        $endDate = $this->reportParams['end_date'] ?? now();

        $customers = User::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 2) // Assuming 2 is customer type
            ->withCount('orders')
            ->get();

        return [
            'title' => 'Customers Report',
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $customers,
            'summary' => [
                'total_customers' => $customers->count(),
                'active_customers' => $customers->where('orders_count', '>', 0)->count(),
            ]
        ];
    }

    /**
     * Generate file based on format
     *
     * @param array $reportData
     * @return string Path to generated file
     */
    protected function generateFile(array $reportData): string
    {
        $filename = $this->reportType . '_' . now()->format('Y-m-d_His') . '.' . $this->outputFormat;
        $filepath = 'reports/' . $filename;

        switch ($this->outputFormat) {
            case 'pdf':
                $pdf = PDF::loadView('reports.' . $this->reportType, $reportData);
                Storage::put($filepath, $pdf->output());
                break;

            case 'csv':
                $this->generateCSV($reportData, $filepath);
                break;

            case 'excel':
                // Implement Excel export if needed
                throw new \Exception('Excel format not yet implemented');

            default:
                throw new \Exception("Unknown output format: {$this->outputFormat}");
        }

        return $filepath;
    }

    /**
     * Generate CSV file
     *
     * @param array $reportData
     * @param string $filepath
     * @return void
     */
    protected function generateCSV(array $reportData, string $filepath)
    {
        $handle = fopen('php://temp', 'w+');

        // Write headers
        if (!empty($reportData['data'])) {
            $firstItem = $reportData['data']->first();
            fputcsv($handle, array_keys($firstItem->toArray()));

            // Write data
            foreach ($reportData['data'] as $item) {
                fputcsv($handle, $item->toArray());
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::put($filepath, $content);
    }

    /**
     * Notify user that report is ready
     *
     * @param string $filePath
     * @return void
     */
    protected function notifyUser(string $filePath)
    {
        $user = User::find($this->userId);

        if ($user && $user->email) {
            // Send email notification with download link
            SendEmailJob::dispatch(
                $user->email,
                'Your report is ready',
                'emails.report_ready',
                [
                    'user' => $user,
                    'report_type' => $this->reportType,
                    'download_link' => Storage::url($filePath)
                ]
            );
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
        Log::error('Report generation job failed after all retries', [
            'type' => $this->reportType,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);

        // Notify user of failure
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user && $user->email) {
                SendEmailJob::dispatch(
                    $user->email,
                    'Report generation failed',
                    'emails.report_failed',
                    [
                        'user' => $user,
                        'report_type' => $this->reportType,
                        'error' => $exception->getMessage()
                    ]
                );
            }
        }
    }
}
