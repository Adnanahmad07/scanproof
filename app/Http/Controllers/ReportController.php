<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\HealthScoreService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
        private HealthScoreService $healthScore,
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $reportData = $this->analytics->getReportData($startDate, $endDate);
        $healthScore = $this->healthScore->calculate();
        $healthLabel = $this->healthScore->getLabel($healthScore);

        return view('pages.admin.reports', compact('reportData', 'healthScore', 'healthLabel', 'startDate', 'endDate'));
    }

    public function pdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $reportData = $this->analytics->getReportData($startDate, $endDate);
        $healthScore = $this->healthScore->calculate();
        $healthLabel = $this->healthScore->getLabel($healthScore);
        $healthColor = $this->healthScore->getColor($healthScore);

        $pdf = Pdf::loadView('pages.admin.report-pdf', compact('reportData', 'healthScore', 'healthLabel', 'healthColor', 'startDate', 'endDate'))
            ->setPaper('a4')
            ->setOptions(['isRemoteEnabled' => true]);

        return $pdf->download("scanproof-report-{$startDate}-to-{$endDate}.pdf");
    }
}
