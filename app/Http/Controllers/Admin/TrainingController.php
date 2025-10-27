<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminTrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    protected $trainingService;

    public function __construct(AdminTrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
        $this->middleware('auth:admin');
    }

    /**
     * Dashboard formation
     */
    public function index()
    {
        $stats = $this->trainingService->getTeamTrainingStats();
        
        return view('admin.training.dashboard', [
            'stats' => $stats['stats'] ?? null,
            'modules' => $this->getAvailableModules()
        ]);
    }

    /**
     * Démarrer nouvelle formation
     */
    public function start(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'user_id' => 'required',
        ]);

        $result = $this->trainingService->startTrainingSession(
            $request->user_id,
            $request->module,
            Auth::user()->name
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'session_id' => $result['session_id'],
                'redirect' => route('admin.training.session', $result['session_id'])
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Session de formation active
     */
    public function session($sessionId)
    {
        // Récupérer données session
        $sessionData = cache()->get("training_session_{$sessionId}");
        
        if (!$sessionData) {
            return redirect()->route('admin.training.index')
                           ->with('error', 'Session de formation introuvable');
        }

        return view('admin.training.session', [
            'session' => $sessionData,
            'sessionId' => $sessionId
        ]);
    }

    /**
     * Contenu section formation
     */
    public function getSectionContent(Request $request, $sessionId)
    {
        $request->validate([
            'section' => 'required|string'
        ]);

        $content = $this->trainingService->getTrainingContent(
            $sessionId, 
            $request->section
        );

        return response()->json($content);
    }

    /**
     * Soumettre évaluation
     */
    public function submitEvaluation(Request $request, $sessionId)
    {
        $request->validate([
            'type' => 'required|in:quiz,practical,simulation',
            'data' => 'required|array'
        ]);

        $result = $this->trainingService->evaluateTraining(
            $sessionId,
            $request->type,
            $request->data
        );

        return response()->json($result);
    }

    /**
     * Générer certificat
     */
    public function generateCertificate($sessionId)
    {
        $result = $this->trainingService->generateCertificate($sessionId);
        
        if ($result['success']) {
            return view('admin.training.certificate', [
                'certificate' => $result['certificate']
            ]);
        }

        return back()->with('error', $result['error']);
    }

    /**
     * Planning formations
     */
    public function schedule()
    {
        return view('admin.training.schedule');
    }

    /**
     * Planifier nouvelle formation
     */
    public function scheduleTraining(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'module' => 'required|string',
            'trainer' => 'required|string',
            'participants' => 'required|array',
            'date' => 'required|date|after:now',
            'duration_hours' => 'required|numeric|min:0.5|max:8',
            'location' => 'nullable|string'
        ]);

        $result = $this->trainingService->scheduleTrainingSession($request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Formation planifiée avec succès',
                'schedule_id' => $result['schedule_id']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Rapports formation
     */
    public function reports()
    {
        $stats = $this->trainingService->getTeamTrainingStats();
        
        return view('admin.training.reports', [
            'stats' => $stats['stats'] ?? null
        ]);
    }

    /**
     * Export données formation
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'json');
        $stats = $this->trainingService->getTeamTrainingStats();
        
        switch ($format) {
            case 'csv':
                return $this->exportCSV($stats['stats']);
            case 'excel':
                return $this->exportExcel($stats['stats']);
            case 'pdf':
                return $this->exportPDF($stats['stats']);
            default:
                return response()->json($stats['stats']);
        }
    }

    /**
     * Modules disponibles
     */
    private function getAvailableModules()
    {
        return [
            'overview' => [
                'title' => 'Vue d\'ensemble RestroSaaS',
                'description' => 'Introduction complète à la plateforme',
                'duration' => 30,
                'difficulty' => 'Débutant',
                'icon' => 'fas fa-info-circle'
            ],
            'admin_interface' => [
                'title' => 'Interface Administration',
                'description' => 'Navigation et utilisation interface admin',
                'duration' => 45,
                'difficulty' => 'Intermédiaire',
                'icon' => 'fas fa-desktop'
            ],
            'customer_support' => [
                'title' => 'Support Client',
                'description' => 'Techniques support client professionnel',
                'duration' => 60,
                'difficulty' => 'Intermédiaire',
                'icon' => 'fas fa-headset'
            ],
            'technical_advanced' => [
                'title' => 'Administration Technique',
                'description' => 'Monitoring, sécurité, performance',
                'duration' => 90,
                'difficulty' => 'Avancé',
                'icon' => 'fas fa-cogs'
            ]
        ];
    }

    /**
     * Export CSV
     */
    private function exportCSV($stats)
    {
        $filename = 'training_stats_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($stats) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Métrique', 'Valeur']);
            
            // Data
            foreach ($stats['overview'] as $key => $value) {
                fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Excel (placeholder)
     */
    private function exportExcel($stats)
    {
        // Implémentation export Excel avec PhpSpreadsheet
        return response()->json(['message' => 'Export Excel à implémenter']);
    }

    /**
     * Export PDF (placeholder)
     */
    private function exportPDF($stats)
    {
        // Implémentation export PDF avec DomPDF/TCPDF
        return response()->json(['message' => 'Export PDF à implémenter']);
    }
}