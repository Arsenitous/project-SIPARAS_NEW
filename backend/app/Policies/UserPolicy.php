<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy — Aturan Access Control untuk User Management
 *
 * ┌──────────────────────────────────────────────────────────┐
 * │ Role         │ Manage Users               │ Manage Self  │
 * ├──────────────┼────────────────────────────┼──────────────┤
 * │ superadmin   │ SEMUA role (full control)  │ ✅           │
 * │ kd_sarpras   │ Semua KECUALI koordinator* │ ✅           │
 * │ admin_sarpras│ kepsek, dept, asp, koor_ski│ ✅           │
 * │ koordinator* │ ❌ (audit only)            │ ✅ (self pw) │
 * │ kepsek/etc   │ ❌                         │ ✅ (self pw) │
 * └──────────────┴────────────────────────────┴──────────────┘
 *
 * *koordinator_ski dan koordinator_utama hanya bisa ganti password sendiri
 */
class UserPolicy
{
    // Roles that koordinator and below cannot touch
    protected const KOORDINATOR_ROLES = ['koordinator_utama', 'koordinator_ski'];

    // Roles that admin_sarpras can manage
    protected const ADMIN_MANAGEABLE = ['kepsek', 'dept_ygpkb', 'asp_wilayah', 'koordinator_ski'];

    // Roles that kd_sarpras can manage (all except koordinator)
    protected const KD_MANAGEABLE = [
        'kepsek', 'dept_ygpkb', 'asp_wilayah',
        'admin_sarpras', 'koordinator_ski',
    ];

    /**
     * Bolehkah user melihat daftar pengguna?
     */
    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, ['superadmin', 'kd_sarpras', 'admin_sarpras']);
    }

    /**
     * Bolehkah actor membuat user baru dengan role tertentu?
     */
    public function create(User $actor, string $targetRole): bool
    {
        if ($actor->role === 'superadmin') return true;
        if ($actor->role === 'kd_sarpras') return in_array($targetRole, self::KD_MANAGEABLE);
        if ($actor->role === 'admin_sarpras') return in_array($targetRole, self::ADMIN_MANAGEABLE);
        return false;
    }

    /**
     * Bolehkah actor mengedit/mengupdate user target?
     */
    public function update(User $actor, User $target): bool
    {
        // Semua user boleh edit diri sendiri (ganti password)
        if ($actor->id === $target->id) return true;

        if ($actor->role === 'superadmin') return true;
        if ($actor->role === 'kd_sarpras') {
            return in_array($target->role, self::KD_MANAGEABLE);
        }
        if ($actor->role === 'admin_sarpras') {
            return in_array($target->role, self::ADMIN_MANAGEABLE);
        }
        return false;
    }

    /**
     * Bolehkah actor mengubah role user target ke role baru?
     */
    public function changeRole(User $actor, User $target, string $newRole): bool
    {
        if ($actor->role === 'superadmin') return true;
        if ($actor->role === 'kd_sarpras') {
            // KD tidak bisa ubah/set role koordinator
            return !in_array($target->role, self::KOORDINATOR_ROLES)
                && !in_array($newRole, self::KOORDINATOR_ROLES)
                && !in_array($newRole, ['superadmin', 'kd_sarpras']);
        }
        if ($actor->role === 'admin_sarpras') {
            return in_array($target->role, self::ADMIN_MANAGEABLE)
                && in_array($newRole, self::ADMIN_MANAGEABLE);
        }
        return false;
    }

    /**
     * Bolehkah actor menonaktifkan / mengaktifkan user target?
     */
    public function toggleActive(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false; // tidak bisa nonaktifkan diri sendiri
        return $this->update($actor, $target);
    }

    /**
     * Bolehkah actor mereset password user target?
     */
    public function resetPassword(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return true; // selalu boleh reset password sendiri
        return $this->update($actor, $target);
    }
}
