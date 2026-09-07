<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function timeline($record_id)
    {
        $logs = AuditLog::where('record_id', $record_id)->orderBy('created_at', 'asc')->get();
        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function auditSystem(Request $request)
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ]
        ]);
    }

    public function recap(Request $request)
    {
        // ── Filter ────────────────────────────────────────────────
        $year  = $request->get('year',  now()->year);
        $month = $request->get('month', null); // optional

        $base = Ticket::query()
            ->whereYear('created_at', $year)
            ->when($month, fn($q) => $q->whereMonth('created_at', $month));

        // ── Global summary ─────────────────────────────────────────
        $total     = (clone $base)->count();
        $approved  = (clone $base)->whereIn('status', ['approved','completed','signed_archived'])->count();
        $rejected  = (clone $base)->where('status', 'rejected')->count();
        $pending   = (clone $base)->whereIn('status', ['submitted_to_asp','submitted_to_kd','draft'])->count();
        $totalCost = (clone $base)->sum('actual_cost');

        // ── By procedure type ──────────────────────────────────────
        $byType = (clone $base)
            ->select('procedure_type', DB::raw('count(*) as total'))
            ->groupBy('procedure_type')
            ->get();

        // ── By unit (top 10) ──────────────────────────────────────
        $byUnit = (clone $base)
            ->select('unit_id', DB::raw('count(*) as total'))
            ->with('unit:id,name,unit_code')
            ->groupBy('unit_id')
            ->orderByDesc('total')
            ->limit(14)
            ->get();

        // ── By month (trend for selected year) ────────────────────
        $byMonth = Ticket::query()
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("approved","completed","signed_archived") THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        // ── By urgency ─────────────────────────────────────────────
        $byUrgency = (clone $base)
            ->select('urgency_level', DB::raw('count(*) as total'))
            ->groupBy('urgency_level')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'filters' => ['year' => (int)$year, 'month' => $month ? (int)$month : null],
                'summary' => compact('total', 'approved', 'rejected', 'pending', 'totalCost'),
                'by_type'    => $byType,
                'by_unit'    => $byUnit,
                'by_month'   => $byMonth,
                'by_urgency' => $byUrgency,
            ]
        ]);
    }

    public function exportExcel(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Fitur ekspor Excel akan segera tersedia.']);
    }

    public function exportPdf(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Fitur ekspor PDF akan segera tersedia.']);
    }
}
