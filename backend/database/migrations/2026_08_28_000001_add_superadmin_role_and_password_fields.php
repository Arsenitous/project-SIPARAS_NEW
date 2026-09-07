<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'superadmin' to users.role ENUM
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM(
            'superadmin',
            'kepsek',
            'dept_ygpkb',
            'asp_wilayah',
            'kd_sarpras',
            'admin_sarpras',
            'koordinator_ski',
            'koordinator_utama'
        ) NOT NULL");

        // Add password_changed_at to track first-login password change
        DB::statement("ALTER TABLE `users` ADD COLUMN `password_changed_at` TIMESTAMP NULL DEFAULT NULL AFTER `password`");
        DB::statement("ALTER TABLE `users` ADD COLUMN `password_must_change` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Harus ganti password saat pertama login' AFTER `password_changed_at`");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` DROP COLUMN IF EXISTS `password_must_change`");
        DB::statement("ALTER TABLE `users` DROP COLUMN IF EXISTS `password_changed_at`");
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM(
            'kepsek', 'dept_ygpkb', 'asp_wilayah', 'kd_sarpras',
            'admin_sarpras', 'koordinator_ski', 'koordinator_utama'
        ) NOT NULL");
    }
};
