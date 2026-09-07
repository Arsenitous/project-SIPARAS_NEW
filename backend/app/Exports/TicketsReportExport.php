<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TicketsReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Ticket::query()
            ->with(['unit', 'room', 'user', 'purchaseOrders'])
            ->when($this->filters['unit_id'] ?? null, fn($q, $uid) => $q->where('unit_id', $uid))
            ->when($this->filters['procedure_type'] ?? null, fn($q, $type) => $q->where('procedure_type', $type))
            ->when($this->filters['status'] ?? null, fn($q, $st) => $q->where('status', $st))
            ->when($this->filters['start_date'] ?? null, fn($q, $start) => $q->whereDate('created_at', '>=', $start))
            ->when($this->filters['end_date'] ?? null, fn($q, $end) => $q->whereDate('created_at', '<=', $end))
            ->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID Tiket',
            'Tanggal Pengajuan',
            'Unit Kerja',
            'Ruangan / Lokasi',
            'Kategori Prosedur',
            'Judul Kendala / Kebutuhan',
            'Tingkat Urgensi',
            'Nomor PO Resmi',
            'Estimasi Anggaran (Rp)',
            'Realisasi Nota Riil (Rp)',
            'Status Akhir'
        ];
    }

    public function map($ticket): array
    {
        $poNumber = $ticket->purchaseOrders->first() ? $ticket->purchaseOrders->first()->po_number : '-';

        return [
            'Tiket #' . $ticket->id,
            $ticket->created_at->format('Y-m-d H:i'),
            $ticket->unit->name,
            $ticket->room ? $ticket->room->name : ($ticket->specific_location ?? 'Non-Permanen'),
            strtoupper($ticket->procedure_type),
            $ticket->title,
            ucfirst($ticket->urgency_level),
            $poNumber,
            $ticket->estimated_cost,
            $ticket->actual_cost ?? 0,
            strtoupper(str_replace('_', ' ', $ticket->status))
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header (Baris 1): Background Biru Tua YGPKB + Teks Putih Bold
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'] // Tailwind Blue-900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Border tipis ke seluruh sel data
        $sheet->getStyle('A1:K' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        return [];
    }
}
