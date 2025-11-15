<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ImportExportService;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\ImportJob;
use App\Models\ExportJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;

class ImportExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $importExportService;
    protected $user;
    protected $restaurant;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->importExportService = new ImportExportService();
        
        // Créer un utilisateur et restaurant de test
        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create(['user_id' => $this->user->id]);
        
        Storage::fake('local');
    }

    /** @test */
    public function it_can_get_available_templates()
    {
        $templates = $this->importExportService->getTemplate('menus');
        
        $this->assertArrayHasKey('required_fields', $templates);
        $this->assertArrayHasKey('all_fields', $templates);
        $this->assertArrayHasKey('validation_rules', $templates);
        
        $this->assertContains('name', $templates['required_fields']);
        $this->assertContains('price', $templates['required_fields']);
    }

    /** @test */
    public function it_validates_menu_template_correctly()
    {
        $template = $this->importExportService->getTemplate('menus');
        
        $this->assertEquals(['name', 'description', 'price', 'cat_id'], $template['required_fields']);
        $this->assertContains('name', $template['all_fields']);
        $this->assertContains('price', $template['all_fields']);
    }

    /** @test */
    public function it_validates_customer_template_correctly()
    {
        $template = $this->importExportService->getTemplate('customers');
        
        $this->assertEquals(['name', 'email'], $template['required_fields']);
        $this->assertContains('phone', $template['all_fields']);
        $this->assertContains('city', $template['all_fields']);
    }

    /** @test */
    public function it_can_analyze_csv_file()
    {
        // Créer un fichier CSV de test
        $csvContent = "name,email,phone\nJohn Doe,john@example.com,123456789\nJane Doe,jane@example.com,987654321";
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);
        
        // Stocker le fichier
        $path = Storage::putFile('temp', $file);
        
        // Analyser le fichier
        $result = $this->importExportService->analyzeFile($path, 'customers');
        
        $this->assertArrayHasKey('upload_id', $result);
        $this->assertArrayHasKey('file_info', $result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('validation', $result);
        
        $this->assertEquals(['name', 'email', 'phone'], $result['preview']['headers']);
        $this->assertEquals(2, $result['preview']['total_rows']);
    }

    /** @test */
    public function it_detects_missing_required_fields()
    {
        // CSV sans champ requis 'email'
        $csvContent = "name,phone\nJohn Doe,123456789";
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);
        $path = Storage::putFile('temp', $file);
        
        $result = $this->importExportService->analyzeFile($path, 'customers');
        
        $this->assertFalse($result['validation']['valid']);
        $this->assertContains('email', $result['validation']['errors'][0]);
    }

    /** @test */
    public function it_can_generate_sample_data()
    {
        $reflection = new \ReflectionClass($this->importExportService);
        $method = $reflection->getMethod('generateSampleData');
        $method->setAccessible(true);
        
        $template = $this->importExportService->getTemplate('menus');
        $sampleData = $method->invoke($this->importExportService, 'menus', $template);
        
        $this->assertIsArray($sampleData);
        $this->assertNotEmpty($sampleData);
        $this->assertArrayHasKey('name', $sampleData[0]);
        $this->assertArrayHasKey('price', $sampleData[0]);
    }

    /** @test */
    public function it_validates_field_rules_correctly()
    {
        $reflection = new \ReflectionClass($this->importExportService);
        $method = $reflection->getMethod('validateFieldRule');
        $method->setAccessible(true);
        
        // Test règle required
        $this->assertTrue($method->invoke($this->importExportService, 'test', 'required', null));
        $this->assertFalse($method->invoke($this->importExportService, '', 'required', null));
        
        // Test règle numeric
        $this->assertTrue($method->invoke($this->importExportService, '123', 'numeric', null));
        $this->assertFalse($method->invoke($this->importExportService, 'abc', 'numeric', null));
        
        // Test règle email
        $this->assertTrue($method->invoke($this->importExportService, 'test@example.com', 'email', null));
        $this->assertFalse($method->invoke($this->importExportService, 'invalid-email', 'email', null));
    }

    /** @test */
    public function it_can_create_import_job()
    {
        $this->actingAs($this->user);
        
        $uploadId = 'test-upload-123';
        $mapping = ['name' => 'name', 'email' => 'email'];
        
        // Mock l'analyse cachée
        cache()->put("import_analysis_{$uploadId}", [
            'type' => 'customers',
            'file_path' => 'temp/test.csv',
            'preview' => ['total_rows' => 10],
            'options' => []
        ], now()->addHours(2));
        
        $job = $this->importExportService->processImport($uploadId, $mapping);
        
        $this->assertInstanceOf(ImportJob::class, $job);
        $this->assertEquals('import', $job->type);
        $this->assertEquals('customers', $job->data_type);
        $this->assertEquals($this->user->id, $job->user_id);
    }

    /** @test */
    public function it_can_create_export_job()
    {
        $this->actingAs($this->user);
        
        $job = $this->importExportService->generateExport(
            'customers', 
            'csv',
            ['date_from' => '2024-01-01'],
            ['include_inactive' => false]
        );
        
        $this->assertInstanceOf(ExportJob::class, $job);
        $this->assertEquals('export', $job->type);
        $this->assertEquals('customers', $job->data_type);
        $this->assertEquals('csv', $job->format);
        $this->assertEquals($this->user->id, $job->user_id);
    }

    /** @test */
    public function it_handles_invalid_file_format()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Format de fichier non supporté');
        
        // Créer un fichier avec extension non supportée
        $file = UploadedFile::fake()->create('test.txt');
        $path = Storage::putFile('temp', $file);
        
        $this->importExportService->analyzeFile($path, 'customers');
    }

    /** @test */
    public function it_handles_missing_analysis_for_import()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Analyse introuvable');
        
        $this->actingAs($this->user);
        
        $this->importExportService->processImport('invalid-upload-id', []);
    }

    /** @test */
    public function it_can_get_dashboard_metrics()
    {
        // Créer quelques jobs de test
        ImportJob::factory()->count(3)->create(['status' => 'completed']);
        ImportJob::factory()->count(2)->create(['status' => 'failed']);
        ExportJob::factory()->count(1)->create(['status' => 'pending']);
        
        $metrics = $this->importExportService->getDashboardMetrics();
        
        $this->assertArrayHasKey('total_jobs', $metrics);
        $this->assertArrayHasKey('completed_jobs', $metrics);
        $this->assertArrayHasKey('failed_jobs', $metrics);
        $this->assertArrayHasKey('success_rate', $metrics);
        
        $this->assertEquals(6, $metrics['total_jobs']);
        $this->assertEquals(3, $metrics['completed_jobs']);
        $this->assertEquals(2, $metrics['failed_jobs']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}