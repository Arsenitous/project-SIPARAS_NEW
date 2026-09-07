<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    /**
     * Generate nomor PO baru secara atomic (mencegah race condition).
     * Format: {urut}/PO/YGPKB/{bulan_romawi}/{tahun}
     * Contoh: 1161/PO/YGPKB/VI/2026
     */
    public function generatePoNumber(): array
    {
        return DB::transaction(function () {
            // Lock row untuk mencegah concurrent generation
            $latest = PurchaseOrder::lockForUpdate()->max('sequence_number');
            $sequence = ($latest ?? 0) + 1;

            $year = now()->year;
            $monthRoman = $this->toRomanMonth(now()->month);
            $poNumber = "{$sequence}/PO/YGPKB/{$monthRoman}/{$year}";

            return [
                'sequence_number' => $sequence,
                'month_roman'     => $monthRoman,
                'year'            => $year,
                'po_number'       => $poNumber,
                'po_date'         => now()->toDateString(),
            ];
        });
    }

    /**
     * Generate nomor BAST.
     * Format: {urut}/BAST-{TYPE}/YGPKB/{bulan_romawi}/{tahun}
     */
    public function generateBastNumber(string $type): string
    {
        $typeUpper = strtoupper($type);
        $year = now()->year;
        $monthRoman = $this->toRomanMonth(now()->month);
        $prefix = "BAST-{$typeUpper}";

        $count = \App\Models\BastDocument::whereYear('created_at', $year)->count() + 1;

        return str_pad($count, 3, '0', STR_PAD_LEFT) . "/{$prefix}/YGPKB/{$monthRoman}/{$year}";
    }

    private function toRomanMonth(int $month): string
    {
        return [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ][$month];
    }
}
