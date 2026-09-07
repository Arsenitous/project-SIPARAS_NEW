<?php

namespace App\Services;

/**
 * ApprovalRoutingService
 *
 * Menentukan jalur routing tiket (Jalur 1–4) berdasarkan
 * prosedur dan role pemohon, sesuai dokumen Project_KP-SIPARAS.
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │ Jalur 1 (jalur_1_sekolah):                                  │
 * │   Pemohon = kepsek | dept_ygpkb                             │
 * │   Alur: Submit → ASP Review → KD Approve → Approved         │
 * │                                                              │
 * │ Jalur 2 (jalur_2_kantor_pusat):                             │
 * │   Pemohon = kepsek | dept_ygpkb, tipe = pengadaan/pemutihan │
 * │   Alur: Submit → KD Approve langsung (bypass ASP)          │
 * │                                                              │
 * │ Jalur 3 (jalur_3_temuan_asp):                               │
 * │   Pemohon = asp_wilayah (temuan lapangan)                   │
 * │   Alur: ASP Submit → KD Approve (bypass review ulang)       │
 * │                                                              │
 * │ Jalur 4 (jalur_4_direct_kd):                                │
 * │   Pemohon = kd_sarpras (top-down)                           │
 * │   Alur: KD Submit → Langsung In-Progress                    │
 * └──────────────────────────────────────────────────────────────┘
 */
class ApprovalRoutingService
{
    /**
     * Tentukan routing_path dan status awal tiket.
     *
     * @param  string $role           Role pemohon
     * @param  string $procedureType  Tipe prosedur tiket
     * @return array  ['routing_path' => ..., 'status' => ..., 'asp_approval_status' => ..., 'kd_approval_status' => ...]
     */
    public function determine(string $role, string $procedureType): array
    {
        // Jalur 4: KD Sarpras membuat tiket secara top-down
        if ($role === 'kd_sarpras') {
            return [
                'routing_path'       => 'jalur_4_direct_kd',
                'status'             => 'in_progress',
                'asp_approval_status'=> 'bypass',
                'kd_approval_status' => 'approved',
            ];
        }

        // Jalur 3: ASP Wilayah menemukan & membuat tiket dari lapangan
        if ($role === 'asp_wilayah') {
            return [
                'routing_path'       => 'jalur_3_temuan_asp',
                'status'             => 'submitted_to_kd',
                'asp_approval_status'=> 'bypass',
                'kd_approval_status' => 'pending',
            ];
        }

        // Jalur 2: Pengadaan atau Pemutihan dari Kepsek/Dept → Langsung ke KD
        if (
            in_array($role, ['kepsek', 'dept_ygpkb']) &&
            in_array($procedureType, ['pengadaan', 'pemutihan'])
        ) {
            return [
                'routing_path'       => 'jalur_2_kantor_pusat',
                'status'             => 'submitted_to_kd',
                'asp_approval_status'=> 'bypass',
                'kd_approval_status' => 'pending',
            ];
        }

        // Jalur 1: Default — Kepsek/Dept dengan tipe perbaikan/mutasi/pemeliharaan → lewat ASP
        return [
            'routing_path'       => 'jalur_1_sekolah',
            'status'             => 'submitted_to_asp',
            'asp_approval_status'=> 'pending',
            'kd_approval_status' => 'pending',
        ];
    }
}
