<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            SET FOREIGN_KEY_CHECKS = 0;

            -- 1. TABEL: units
            DROP TABLE IF EXISTS `units`;
            CREATE TABLE `units` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `unit_code` VARCHAR(30) NOT NULL UNIQUE COMMENT 'Kode unik: TK_1, SMP_1, SMA_IBC, ITBSS, KANTOR_PUSAT',
            `name` VARCHAR(150) NOT NULL COMMENT 'Nama resmi unit sekolah / kantor yayasan',
            `category` ENUM('persekolahan', 'perguruan_tinggi', 'kantor_pusat') NOT NULL DEFAULT 'persekolahan',
            `asp_territory` ENUM('kubu_raya', 'regu', 'ibc', 'kota', 'itbss', 'none') NOT NULL DEFAULT 'none',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL,
            INDEX `idx_units_asp_territory` (`asp_territory`),
            INDEX `idx_units_category` (`category`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



            -- 3. TABEL: users
            DROP TABLE IF EXISTS `users`;
            CREATE TABLE `users` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `unit_id` BIGINT UNSIGNED NULL COMMENT 'Terkunci ke 1 unit khusus Kepsek & Dept YGPKB, NULL untuk eksekutif/ASP lintas unit',
            `name` VARCHAR(120) NOT NULL,
            `username` VARCHAR(60) NOT NULL UNIQUE,
            `email` VARCHAR(120) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('kepsek', 'dept_ygpkb', 'asp_wilayah', 'kd_sarpras', 'admin_sarpras', 'koordinator_ski', 'koordinator_utama') NOT NULL,
            `asp_territory` ENUM('kubu_raya', 'regu', 'ibc', 'kota', 'itbss', 'none') NOT NULL DEFAULT 'none',
            `phone_number` VARCHAR(30) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0: Ditangguhkan sementara, 1: Aktif',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete: Dihapus permanen',
            CONSTRAINT `fk_users_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            INDEX `idx_users_role` (`role`),
            INDEX `idx_users_asp_territory` (`asp_territory`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 4. TABEL: vendors
            DROP TABLE IF EXISTS `vendors`;
            CREATE TABLE `vendors` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `pic_name` VARCHAR(100) NULL,
            `phone` VARCHAR(40) NULL,
            `address` TEXT NULL,
            `specialty` VARCHAR(100) NULL COMMENT 'Kategori: AC, Las, Sipil, Listrik, ATK, Elektronik',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0: Non-aktif sementara, 1: Aktif normal',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete: Dihapus permanen',
            INDEX `idx_vendors_name` (`name`),
            INDEX `idx_vendors_is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 5. document_signatories (Dihapus / Tidak Digunakan)
            DROP TABLE IF EXISTS `document_signatories`;

            -- 5. TABEL: tickets
            DROP TABLE IF EXISTS `tickets`;
            CREATE TABLE `tickets` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `unit_id` BIGINT UNSIGNED NOT NULL COMMENT 'Unit pemohon / lokasi aset',
            `location_name` VARCHAR(150) NULL COMMENT 'Nama ruangan/lokasi (free text)',
            `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Akun pemohon tiket',
            `preventive_schedule_id` BIGINT UNSIGNED NULL COMMENT 'Rujukan kalender preventif jika servis terjadwal',
            
            `procedure_type` ENUM('perbaikan', 'pemeliharaan', 'pengadaan', 'mutasi', 'pemutihan') NOT NULL,
            `routing_path` ENUM('jalur_1_sekolah', 'jalur_2_kantor_pusat', 'jalur_3_temuan_asp', 'jalur_4_direct_kd') NOT NULL,
            
            `target_object_category` ENUM('fasilitas_ruangan', 'barang_inventaris') NULL DEFAULT 'fasilitas_ruangan' COMMENT 'Khusus Prosedur Perbaikan (NULL untuk prosedur lain)',
            `specific_location` VARCHAR(255) NULL COMMENT 'Detail titik lokasi kerusakan jika fasilitas ruangan',
            `item_name` VARCHAR(150) NULL COMMENT 'Nama/merk barang jika inventaris',
            `inventory_code` VARCHAR(80) NULL DEFAULT '-' COMMENT 'Nomor label inventaris atau -',
            
            `target_unit_id` BIGINT UNSIGNED NULL COMMENT 'Unit tujuan pemindahan',
            `target_location_name` VARCHAR(150) NULL COMMENT 'Ruangan/lokasi tujuan mutasi (free text)',
            
            `disposal_action` ENUM('dimusnahkan', 'dijual', 'dialihkan_disumbangkan') NULL,
            `disposal_destination_or_notes` TEXT NULL,
            `estimated_salvage_value` DECIMAL(15,2) NULL DEFAULT 0.00,
            
            `title` VARCHAR(200) NOT NULL COMMENT 'Judul ringkas kendala / usulan',
            `urgency_level` ENUM('rendah', 'sedang', 'tinggi_darurat') NOT NULL DEFAULT 'sedang',
            `problem_cause` TEXT NULL COMMENT 'Uraian masalah / penyebab kerusakan / justifikasi',
            `action_plan` TEXT NULL COMMENT 'Rencana teknis perbaikan / pengadaan / perawatan',
            `estimated_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            
            `asp_approval_status` ENUM('pending', 'approved', 'revision_required', 'rejected', 'bypass') NOT NULL DEFAULT 'pending',
            `asp_approval_by` BIGINT UNSIGNED NULL,
            `asp_approval_at` TIMESTAMP NULL,
            `asp_notes` TEXT NULL,
            
            `kd_approval_status` ENUM('pending', 'approved', 'revision_required', 'rejected', 'bypass') NOT NULL DEFAULT 'pending',
            `kd_approval_by` BIGINT UNSIGNED NULL,
            `kd_approval_at` TIMESTAMP NULL,
            `kd_notes` TEXT NULL,
            
            `status` ENUM('submitted_to_asp', 'submitted_to_kd', 'revision_required', 'approved', 'in_progress', 'signed_archived', 'completed', 'rejected') NOT NULL DEFAULT 'submitted_to_asp',
            
            `actual_cost` DECIMAL(15,2) NULL DEFAULT NULL COMMENT 'Nilai kuitansi nota pelunasan fisik',
            
            `completed_at` TIMESTAMP NULL,
            `completed_by` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_tickets_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_tickets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_tickets_target_unit_id` FOREIGN KEY (`target_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_tickets_asp_approval_by` FOREIGN KEY (`asp_approval_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_tickets_kd_approval_by` FOREIGN KEY (`kd_approval_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_tickets_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            
            INDEX `idx_tickets_status` (`status`),
            INDEX `idx_tickets_procedure_type` (`procedure_type`),
            INDEX `idx_tickets_unit_created` (`unit_id`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 7. TABEL: ticket_items
            DROP TABLE IF EXISTS `ticket_items`;
            CREATE TABLE `ticket_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` BIGINT UNSIGNED NOT NULL,
            `inventory_code` VARCHAR(80) NULL DEFAULT '-',
            `item_description` VARCHAR(255) NOT NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `unit_metric` VARCHAR(30) NOT NULL DEFAULT 'Unit',
            `condition_status` ENUM('baik', 'rusak_ringan', 'rusak_berat', 'afkir') NULL,
            `estimated_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `estimated_subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `item_notes` VARCHAR(255) NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_ticket_items_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX `idx_ticket_items_ticket_id` (`ticket_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 8. TABEL: ticket_photos
            DROP TABLE IF EXISTS `ticket_photos`;
            CREATE TABLE `ticket_photos` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` BIGINT UNSIGNED NOT NULL,
            `photo_type` ENUM('photo_before', 'photo_after', 'photo_evidence', 'photo_handover') NOT NULL,
            `file_path` VARCHAR(255) NOT NULL COMMENT 'Lokasi file',
            `file_size_kb` INT UNSIGNED NOT NULL DEFAULT 0,
            `caption` VARCHAR(150) NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_ticket_photos_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX `idx_ticket_photos_ticket_id` (`ticket_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 9. TABEL: purchase_orders
            DROP TABLE IF EXISTS `purchase_orders`;
            CREATE TABLE `purchase_orders` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` BIGINT UNSIGNED NULL COMMENT 'Terkait tiket (1:N), atau NULL jika PO Bebas Direct KD',
            `unit_id` BIGINT UNSIGNED NOT NULL,
            `vendor_id` BIGINT UNSIGNED NULL,
            `year` SMALLINT UNSIGNED NOT NULL,
            `sequence_number` INT UNSIGNED NULL,
            `po_number` VARCHAR(60) NULL UNIQUE,
            `vendor_name_snapshot` VARCHAR(150) NOT NULL,
            `vendor_pic_snapshot` VARCHAR(100) NULL,
            `vendor_phone_snapshot` VARCHAR(40) NULL,
            `vendor_address_snapshot` TEXT NULL,
            `is_incidental_vendor` TINYINT(1) NOT NULL DEFAULT 0,
            `is_new_vendor_proposal` TINYINT(1) NOT NULL DEFAULT 0,
            `proposed_vendor_specialty` VARCHAR(100) NULL,
            `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `terbilang_text` TEXT NOT NULL,
            `po_status` ENUM('draft', 'pending_approval', 'pending_signature', 'signed_archived', 'completed', 'rejected') NOT NULL DEFAULT 'draft',
            `scan_document_path` VARCHAR(255) NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `approved_by` BIGINT UNSIGNED NULL,
            `approved_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_po_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk_po_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_po_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk_po_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_po_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            INDEX `idx_po_ticket_id` (`ticket_id`),
            INDEX `idx_po_year_seq` (`year`, `sequence_number`),
            INDEX `idx_po_status` (`po_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 10. TABEL: po_items
            DROP TABLE IF EXISTS `po_items`;
            CREATE TABLE `po_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `purchase_order_id` BIGINT UNSIGNED NOT NULL,
            `item_description` VARCHAR(255) NOT NULL,
            `order_quantity` INT NOT NULL DEFAULT 1,
            `unit_metric` VARCHAR(30) NOT NULL DEFAULT 'Pcs',
            `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `item_subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `notes` VARCHAR(255) NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_po_items_purchase_order_id` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX `idx_po_items_po_id` (`purchase_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 11. TABEL: bast_documents
            DROP TABLE IF EXISTS `bast_documents`;
            CREATE TABLE `bast_documents` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `bast_type` ENUM('perbaikan', 'pemeliharaan', 'pengadaan', 'mutasi', 'pemutihan') NOT NULL,
            `handover_date` DATE NOT NULL COMMENT 'Tanggal serah terima / tuntas fisik',
            `executor_or_vendor_name` VARCHAR(150) NULL COMMENT 'Nama teknisi / pengirim barang',
            `actual_cost` DECIMAL(15,2) NULL DEFAULT 0.00,
            `general_notes` TEXT NULL,
            `scan_document_path` VARCHAR(255) NULL COMMENT 'Lokasi file PDF scan BAST bertanda tangan basah',
            `status` ENUM('draft', 'pending_signature', 'signed_archived') NOT NULL DEFAULT 'draft',
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_bast_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_bast_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            INDEX `idx_bast_type` (`bast_type`),
            INDEX `idx_bast_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 12. TABEL: preventive_schedules
            DROP TABLE IF EXISTS `preventive_schedules`;
            CREATE TABLE `preventive_schedules` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `unit_id` BIGINT UNSIGNED NOT NULL,
            `location_name` VARCHAR(150) NULL COMMENT 'Nama ruangan/lokasi aset (free text)',
            `facility_name` VARCHAR(150) NOT NULL COMMENT 'Nama fasilitas: AC Panasonic 2 PK, Genset Perkins',
            `inventory_code` VARCHAR(80) NULL DEFAULT '-',
            `frequency_months` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Interval servis (bulan)',
            `last_service_date` DATE NOT NULL,
            `next_due_date` DATE GENERATED ALWAYS AS (DATE_ADD(`last_service_date`, INTERVAL `frequency_months` MONTH)) STORED COMMENT 'Kalkulasi otomatis basis data',
            `default_vendor_id` BIGINT UNSIGNED NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_prev_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_prev_vendor_id` FOREIGN KEY (`default_vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            INDEX `idx_prev_next_due_date` (`next_due_date`),
            INDEX `idx_prev_unit_id` (`unit_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- 13. TABEL: audit_logs
            DROP TABLE IF EXISTS `audit_logs`;
            CREATE TABLE `audit_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `module` ENUM('perbaikan', 'pemeliharaan', 'pengadaan', 'mutasi', 'pemutihan', 'master_vendor', 'purchase_order', 'user_auth') NOT NULL,
            `record_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID tiket / ID dokumen terkait',
            `user_id` BIGINT UNSIGNED NOT NULL,
            `role` VARCHAR(50) NOT NULL,
            `action` VARCHAR(50) NOT NULL COMMENT 'SUBMIT, ASP_APPROVE, KD_APPROVE, REVISE, REJECT, UPDATE_ESTIMATE, ISSUE_PO_NUMBER, UPLOAD_SCAN, ACTUAL_COST, COMPLETED',
            `description` VARCHAR(255) NOT NULL,
            `old_values` JSON NULL,
            `new_values` JSON NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_audit_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            INDEX `idx_audit_module_record` (`module`, `record_id`),
            INDEX `idx_audit_user_created` (`user_id`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            -- FIX CIRCULAR FK TICKETS <-> PREVENTIVE_SCHEDULES
            ALTER TABLE `tickets` ADD CONSTRAINT `fk_tickets_prev_id` FOREIGN KEY (`preventive_schedule_id`) REFERENCES `preventive_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

            -- TRIGGERS
            DROP TRIGGER IF EXISTS `trg_audit_logs_prevent_update`;
            CREATE TRIGGER `trg_audit_logs_prevent_update`
            BEFORE UPDATE ON `audit_logs`
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Operasi Ditolak: Data pada tabel audit_logs bersifat append-only dan tidak boleh diubah.';
            END;

            DROP TRIGGER IF EXISTS `trg_audit_logs_prevent_delete`;
            CREATE TRIGGER `trg_audit_logs_prevent_delete`
            BEFORE DELETE ON `audit_logs`
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Operasi Ditolak: Data pada tabel audit_logs bersifat permanen dan tidak boleh dihapus.';
            END;

            SET FOREIGN_KEY_CHECKS = 1;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            SET FOREIGN_KEY_CHECKS = 0;
            DROP TABLE IF EXISTS `audit_logs`;
            ALTER TABLE `tickets` DROP FOREIGN KEY `fk_tickets_prev_id`;
            DROP TABLE IF EXISTS `preventive_schedules`;
            DROP TABLE IF EXISTS `bast_documents`;
            DROP TABLE IF EXISTS `po_items`;
            DROP TABLE IF EXISTS `purchase_orders`;
            DROP TABLE IF EXISTS `ticket_photos`;
            DROP TABLE IF EXISTS `ticket_items`;
            DROP TABLE IF EXISTS `tickets`;
            DROP TABLE IF EXISTS `document_signatories`;
            DROP TABLE IF EXISTS `vendors`;
            DROP TABLE IF EXISTS `users`;
            DROP TABLE IF EXISTS `rooms`;
            DROP TABLE IF EXISTS `units`;
            SET FOREIGN_KEY_CHECKS = 1;
        ");
    }
};
