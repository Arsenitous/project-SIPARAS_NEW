<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BastDocument;

class BastDocumentController extends Controller
{
    public function show($ticket_id)
    {
        $bast = BastDocument::where('ticket_id', $ticket_id)->first();
        if (!$bast) {
            return response()->json([
                'success' => false,
                'message' => 'BAST tidak ditemukan',
                'data' => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $bast
        ]);
    }

    public function generate(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'BAST berhasil di-generate',
            'data' => []
        ], 201);
    }

    public function downloadPdf($ticket_id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Belum diimplementasikan'
        ]);
    }

    public function uploadScan(Request $request, $ticket_id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Scan BAST berhasil diunggah'
        ]);
    }
}
