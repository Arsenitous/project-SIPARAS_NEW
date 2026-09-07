<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use App\Models\DocumentSignatory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Seeding SIPARAS Master Schema...');

        // ═══════════════════════════════════════════════════════════
        // 1. SEED 14 UNIT OPERASIONAL YGPKB
        // Nama sesuai dokumen resmi, urutan sesuai permintaan
        // ═══════════════════════════════════════════════════════════

        // ── WILAYAH: KOTA (TK1, SD1) ──────────────────────────────
        $tk1  = Unit::create(['unit_code' => 'TK-1',   'name' => 'TK 1',   'category' => 'persekolahan', 'asp_territory' => 'kota']);
        $sd1  = Unit::create(['unit_code' => 'SD-1',   'name' => 'SD 1',   'category' => 'persekolahan', 'asp_territory' => 'kota']);

        // ── WILAYAH: REGU (SMP1, SMK1, SMA Regu, SMP2, SMK2) ───────
        $smp1 = Unit::create(['unit_code' => 'SMP-1',  'name' => 'SMP 1',  'category' => 'persekolahan', 'asp_territory' => 'regu']);
        $smk1 = Unit::create(['unit_code' => 'SMK-1',  'name' => 'SMK 1',  'category' => 'persekolahan', 'asp_territory' => 'regu']);
        $smaRegu = Unit::create(['unit_code' => 'SMA-REGU', 'name' => 'SMA Regu', 'category' => 'persekolahan', 'asp_territory' => 'regu']);

        // ── WILAYAH: KUBU RAYA (TK2, SD2, SMP2, SMK2) ───────────────────────────────
        $tk2  = Unit::create(['unit_code' => 'TK-2',   'name' => 'TK 2',   'category' => 'persekolahan', 'asp_territory' => 'kubu_raya']);
        $sd2  = Unit::create(['unit_code' => 'SD-2',   'name' => 'SD 2',   'category' => 'persekolahan', 'asp_territory' => 'kubu_raya']);
        $smp2 = Unit::create(['unit_code' => 'SMP-2',  'name' => 'SMP 2',  'category' => 'persekolahan', 'asp_territory' => 'kubu_raya']);
        $smk2 = Unit::create(['unit_code' => 'SMK-2',  'name' => 'SMK 2',  'category' => 'persekolahan', 'asp_territory' => 'kubu_raya']);

        // ── WILAYAH: IBC (SD IBC, SMP IBC, SMA IBC) ─────────────────
        $sdIbc  = Unit::create(['unit_code' => 'SD-IBC',  'name' => 'SD IBC',  'category' => 'persekolahan', 'asp_territory' => 'ibc']);
        $smpIbc = Unit::create(['unit_code' => 'SMP-IBC', 'name' => 'SMP IBC', 'category' => 'persekolahan', 'asp_territory' => 'ibc']);
        $smaIbc = Unit::create(['unit_code' => 'SMA-IBC', 'name' => 'SMA IBC', 'category' => 'persekolahan', 'asp_territory' => 'ibc']);

        // ── KANTOR PUSAT (tanpa wilayah ASP) ────────────────────────
        $ygpkb  = Unit::create(['unit_code' => 'YGPKB',  'name' => 'YGPKB',  'category' => 'kantor_pusat',    'asp_territory' => 'none']);

        // ── WILAYAH: ITBSS (Perguruan Tinggi) ────────────────────────
        $itbss  = Unit::create(['unit_code' => 'ITBSS',  'name' => 'ITBSS',  'category' => 'perguruan_tinggi', 'asp_territory' => 'itbss']);

        $this->command->info('✅ 14 unit operasional berhasil dibuat.');

        // ═══════════════════════════════════════════════════════════
        // 2. SEED USERS — Setiap Kepsek terkunci ke 1 unit spesifik
        // ═══════════════════════════════════════════════════════════
        $pass = Hash::make('password');

        // ── SUPERADMIN ───────────────────────────────────────────────
        User::create([
            'unit_id' => null, 'name' => 'Super Administrator', 'username' => 'superadmin',
            'email' => 'superadmin@ygpkb.ac.id', 'password' => Hash::make('Admin@YGPKB2026!'),
            'role' => 'superadmin', 'is_active' => true, 'password_must_change' => false,
        ]);

        // ── KEPSEK — setiap kepsek terikat ke unit_id spesifiknya ────
        $kepsekUnits = [
            ['unit' => $tk1,     'name' => 'Kepsek TK 1',     'username' => 'kepsek_tk1',     'email' => 'kepsek.tk1@ygpkb.ac.id'],
            ['unit' => $sd1,     'name' => 'Kepsek SD 1',     'username' => 'kepsek_sd1',     'email' => 'kepsek.sd1@ygpkb.ac.id'],
            ['unit' => $smp1,    'name' => 'Kepsek SMP 1',    'username' => 'kepsek_smp1',    'email' => 'kepsek.smp1@ygpkb.ac.id'],
            ['unit' => $smk1,    'name' => 'Kepsek SMK 1',    'username' => 'kepsek_smk1',    'email' => 'kepsek.smk1@ygpkb.ac.id'],
            ['unit' => $smaRegu, 'name' => 'Kepsek SMA Regu', 'username' => 'kepsek_sma_regu','email' => 'kepsek.smaregu@ygpkb.ac.id'],
            ['unit' => $tk2,     'name' => 'Kepsek TK 2',     'username' => 'kepsek_tk2',     'email' => 'kepsek.tk2@ygpkb.ac.id'],
            ['unit' => $sd2,     'name' => 'Kepsek SD 2',     'username' => 'kepsek_sd2',     'email' => 'kepsek.sd2@ygpkb.ac.id'],
            ['unit' => $smp2,    'name' => 'Kepsek SMP 2',    'username' => 'kepsek_smp2',    'email' => 'kepsek.smp2@ygpkb.ac.id'],
            ['unit' => $smk2,    'name' => 'Kepsek SMK 2',    'username' => 'kepsek_smk2',    'email' => 'kepsek.smk2@ygpkb.ac.id'],
            ['unit' => $sdIbc,   'name' => 'Kepsek SD IBC',   'username' => 'kepsek_sd_ibc',  'email' => 'kepsek.sdibc@ygpkb.ac.id'],
            ['unit' => $smpIbc,  'name' => 'Kepsek SMP IBC',  'username' => 'kepsek_smp_ibc', 'email' => 'kepsek.smpibc@ygpkb.ac.id'],
            ['unit' => $smaIbc,  'name' => 'Kepsek SMA IBC',  'username' => 'kepsek_sma_ibc', 'email' => 'kepsek.smaibc@ygpkb.ac.id'],
        ];

        foreach ($kepsekUnits as $ku) {
            User::create([
                'unit_id' => $ku['unit']->id,
                'name'    => $ku['name'],
                'username'=> $ku['username'],
                'email'   => $ku['email'],
                'password'=> $pass,
                'role'    => 'kepsek',
                'is_active' => true,
                'password_must_change' => false,
            ]);
        }

        // PIC ITBSS (role kepsek tapi untuk kampus ITBSS)
        User::create([
            'unit_id' => $itbss->id, 'name' => 'PIC ITBSS', 'username' => 'kepsek_itbss',
            'email' => 'pic.itbss@ygpkb.ac.id', 'password' => $pass,
            'role' => 'kepsek', 'is_active' => true, 'password_must_change' => false,
        ]);

        // Dept YGPKB (Kantor Pusat)
        User::create([
            'unit_id' => $ygpkb->id, 'name' => 'Kepala Dept GA', 'username' => 'dept_ga',
            'email' => 'dept.ga@ygpkb.ac.id', 'password' => $pass,
            'role' => 'dept_ygpkb', 'is_active' => true, 'password_must_change' => false,
        ]);

        // ── ASP WILAYAH (5 wilayah) ──────────────────────────────────
        $aspData = [
            ['username' => 'asp_kubu_raya', 'name' => 'ASP Wilayah Kubu Raya', 'email' => 'asp.kuburai@ygpkb.ac.id',  'territory' => 'kubu_raya'],
            ['username' => 'asp_regu',      'name' => 'ASP Wilayah Regu',      'email' => 'asp.regu@ygpkb.ac.id',     'territory' => 'regu'],
            ['username' => 'asp_ibc',       'name' => 'ASP Wilayah IBC',       'email' => 'asp.ibc@ygpkb.ac.id',      'territory' => 'ibc'],
            ['username' => 'asp_kota',      'name' => 'ASP Wilayah Kota',      'email' => 'asp.kota@ygpkb.ac.id',     'territory' => 'kota'],
            ['username' => 'asp_itbss',     'name' => 'ASP Wilayah ITBSS',     'email' => 'asp.itbss@ygpkb.ac.id',    'territory' => 'itbss'],
        ];
        foreach ($aspData as $asp) {
            User::create([
                'unit_id' => null, 'name' => $asp['name'], 'username' => $asp['username'],
                'email' => $asp['email'], 'password' => $pass,
                'role' => 'asp_wilayah', 'asp_territory' => $asp['territory'],
                'is_active' => true, 'password_must_change' => false,
            ]);
        }

        // ── KD SARPRAS ────────────────────────────────────────────────
        User::create([
            'unit_id' => null, 'name' => 'Benny (KD Sarpras)', 'username' => 'kd_sarpras',
            'email' => 'kdsarpras@ygpkb.ac.id', 'password' => $pass,
            'role' => 'kd_sarpras', 'is_active' => true, 'password_must_change' => false,
        ]);

        // ── ADMIN SARPRAS ─────────────────────────────────────────────
        User::create([
            'unit_id' => null, 'name' => 'Admin Sarpras', 'username' => 'admin',
            'email' => 'admin@ygpkb.ac.id', 'password' => $pass,
            'role' => 'admin_sarpras', 'is_active' => true, 'password_must_change' => false,
        ]);

        // ── KOORDINATOR UTAMA ─────────────────────────────────────────
        User::create([
            'unit_id' => null, 'name' => 'Christiana Kwee', 'username' => 'koor_utama',
            'email' => 'koorutama@ygpkb.ac.id', 'password' => $pass,
            'role' => 'koordinator_utama', 'is_active' => true, 'password_must_change' => false,
        ]);

        // ── KOORDINATOR SKI ───────────────────────────────────────────
        User::create([
            'unit_id' => null, 'name' => 'Koordinator SKI', 'username' => 'koor_ski',
            'email' => 'koorski@ygpkb.ac.id', 'password' => $pass,
            'role' => 'koordinator_ski', 'is_active' => true, 'password_must_change' => false,
        ]);

        $this->command->info('✅ Users berhasil dibuat (semua role, setiap kepsek terkunci ke unitnya).');

        // ═══════════════════════════════════════════════════════════
        // 3. SEED VENDOR
        // ═══════════════════════════════════════════════════════════
        Vendor::create([
            'name' => 'PT. Sarana Mandiri', 'pic_name' => 'Budi Santoso',
            'phone' => '081234567890', 'address' => 'Jl. Ayani No. 99, Pontianak',
            'specialty' => 'Perbaikan Sipil & Atap', 'is_active' => true
        ]);
        Vendor::create([
            'name' => 'CV. Teknindo Pratama', 'pic_name' => 'Andi Wijaya',
            'phone' => '082211334455', 'address' => 'Jl. Veteran No. 12, Pontianak',
            'specialty' => 'Instalasi Listrik & AC', 'is_active' => true
        ]);

        // ═══════════════════════════════════════════════════════════
        // 4. SEED DOCUMENT SIGNATORIES
        // ═══════════════════════════════════════════════════════════
        DocumentSignatory::create([
            'document_type' => 'bast_perbaikan', 'column_position' => 'left',
            'signature_label' => 'Pelaksana Pekerjaan', 'default_signer_name' => 'Budi Santoso',
            'default_signer_role' => 'Pihak Ketiga / Vendor', 'is_active' => true
        ]);
        DocumentSignatory::create([
            'document_type' => 'bast_perbaikan', 'column_position' => 'center',
            'signature_label' => 'Mengetahui', 'default_signer_name' => 'Benny',
            'default_signer_role' => 'KD Sarpras YGPKB', 'is_active' => true
        ]);
        DocumentSignatory::create([
            'document_type' => 'bast_perbaikan', 'column_position' => 'right',
            'signature_label' => 'Menyetujui', 'default_signer_name' => 'Christiana Kwee',
            'default_signer_role' => 'Koordinator Utama YGPKB', 'is_active' => true
        ]);

        $this->command->info('🎉 SIPARAS seeded! 14 units, all roles, kepsek terkunci ke unitnya masing-masing.');
    }
}
