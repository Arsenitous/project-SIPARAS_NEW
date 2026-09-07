<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreventiveSchedule;

class PreventiveScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = PreventiveSchedule::with(['unit', 'room']);
        $schedules = $query->get();
        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Jadwal servis rutin didaftarkan',
            'data' => []
        ], 201);
    }

    public function triggerService(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Tiket pemeliharaan terjadwal berhasil dibuat',
            'data' => []
        ]);
    }
}
