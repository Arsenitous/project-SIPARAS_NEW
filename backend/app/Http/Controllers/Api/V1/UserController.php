<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        protected UserPolicy $policy,
        protected AuditLogService $audit,
    ) {}

    /**
     * GET /users — Daftar user dengan filter
     */
    public function index(Request $request)
    {
        $actor = $request->user();

        if (!$this->policy->viewAny($actor)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $query = User::with('unit')->whereNull('deleted_at');

        // Superadmin sees all; others see based on their manageable roles
        if ($actor->role === 'kd_sarpras') {
            $query->whereIn('role', ['kepsek', 'dept_ygpkb', 'asp_wilayah', 'admin_sarpras', 'koordinator_ski']);
        } elseif ($actor->role === 'admin_sarpras') {
            $query->whereIn('role', ['kepsek', 'dept_ygpkb', 'asp_wilayah', 'koordinator_ski']);
        }
        // superadmin: no filter — sees all

        // Filters
        if ($request->filled('role'))      $query->where('role', $request->role);
        if ($request->filled('unit_id'))   $query->where('unit_id', $request->unit_id);
        if ($request->filled('is_active')) $query->where('is_active', $request->boolean('is_active'));
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('username', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $users->items(),
            'meta'    => ['current_page' => $users->currentPage(), 'per_page' => $users->perPage(), 'total' => $users->total(), 'last_page' => $users->lastPage()],
        ]);
    }

    /**
     * POST /users — Buat user baru dengan password auto-generate
     */
    public function store(Request $request)
    {
        $actor = $request->user();
        $targetRole = $request->input('role');

        if (!$this->policy->create($actor, $targetRole)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk membuat user dengan role ini.'], 403);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:120',
            'username'      => 'required|string|max:60|unique:users,username',
            'email'         => 'required|email|max:120|unique:users,email',
            'role'          => ['required', Rule::in(['superadmin','kepsek','dept_ygpkb','asp_wilayah','kd_sarpras','admin_sarpras','koordinator_ski','koordinator_utama'])],
            'unit_id'       => 'nullable|exists:units,id',
            'asp_territory' => 'nullable|in:kubu_raya,regu,ibc,kota,itbss,none',
            'phone_number'  => 'nullable|string|max:30',
        ], [
            'username.unique' => 'Username ini sudah digunakan.',
            'email.unique'    => 'Email ini sudah terdaftar.',
        ]);

        // Auto-generate password (8 chars: 2 uppercase, 2 numbers, 4 lowercase)
        $rawPassword = Str::upper(Str::random(2)) . rand(10, 99) . Str::random(4);

        $user = User::create(array_merge($validated, [
            'password'           => Hash::make($rawPassword),
            'password_must_change'=> true,
            'is_active'          => true,
        ]));

        $this->audit->log([
            'module'      => 'user_auth',
            'record_id'   => $user->id,
            'user_id'     => $actor->id,
            'role'        => $actor->role,
            'action'      => 'CREATE_USER',
            'description' => "{$actor->name} membuat akun baru: {$user->name} ({$user->role}).",
            'new_values'  => ['username' => $user->username, 'role' => $user->role],
        ], $request);

        return response()->json([
            'success'          => true,
            'message'          => 'Akun berhasil dibuat.',
            'data'             => $user->load('unit'),
            'generated_password' => $rawPassword, // Hanya dikembalikan sekali, tampilkan ke admin
        ], 201);
    }

    /**
     * GET /users/{id} — Detail user
     */
    public function show(Request $request, $id)
    {
        $actor = $request->user();
        $target = User::with('unit')->findOrFail($id);

        if ($actor->id !== $target->id && !$this->policy->update($actor, $target)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['success' => true, 'data' => $target]);
    }

    /**
     * PUT /users/{id} — Update profil user
     */
    public function update(Request $request, $id)
    {
        $actor  = $request->user();
        $target = User::findOrFail($id);

        if (!$this->policy->update($actor, $target)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:120',
            'email'         => ['sometimes', 'email', 'max:120', Rule::unique('users')->ignore($target->id)],
            'unit_id'       => 'nullable|exists:units,id',
            'asp_territory' => 'nullable|in:kubu_raya,regu,ibc,kota,itbss,none',
            'phone_number'  => 'nullable|string|max:30',
        ]);

        // Role change: check separately
        if ($request->filled('role') && $request->role !== $target->role) {
            if (!$this->policy->changeRole($actor, $target, $request->role)) {
                return response()->json(['success' => false, 'message' => 'Anda tidak berwenang mengubah role ini.'], 403);
            }
            $validated['role'] = $request->role;
        }

        $old = $target->only(['name', 'email', 'role', 'unit_id']);
        $target->update($validated);

        $this->audit->log([
            'module'      => 'user_auth',
            'record_id'   => $target->id,
            'user_id'     => $actor->id,
            'role'        => $actor->role,
            'action'      => 'UPDATE_USER',
            'description' => "{$actor->name} memperbarui profil: {$target->name}.",
            'old_values'  => $old,
            'new_values'  => $validated,
        ], $request);

        return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui.', 'data' => $target->fresh('unit')]);
    }

    /**
     * POST /users/{id}/change-password — User ganti password sendiri atau admin reset
     */
    public function changePassword(Request $request, $id)
    {
        $actor  = $request->user();
        $target = User::findOrFail($id);

        if (!$this->policy->resetPassword($actor, $target)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $isSelf = $actor->id === $target->id;

        $rules = ['new_password' => 'required|string|min:8|confirmed'];
        if ($isSelf && !$target->password_must_change) {
            $rules['current_password'] = 'required|string';
        }

        $validated = $request->validate($rules, [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min'       => 'Password baru minimal 8 karakter.',
        ]);

        // Verify current password if self-change (and not first login)
        if ($isSelf && !$target->password_must_change && isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $target->password)) {
                return response()->json(['success' => false, 'message' => 'Password lama tidak sesuai.'], 422);
            }
        }

        $target->update([
            'password'             => Hash::make($validated['new_password']),
            'password_must_change' => false,
            'password_changed_at'  => now(),
        ]);

        $this->audit->log([
            'module'      => 'user_auth',
            'record_id'   => $target->id,
            'user_id'     => $actor->id,
            'role'        => $actor->role,
            'action'      => $isSelf ? 'CHANGE_OWN_PASSWORD' : 'RESET_USER_PASSWORD',
            'description' => $isSelf
                ? "{$actor->name} mengubah password sendiri."
                : "{$actor->name} mereset password untuk: {$target->name}.",
        ], $request);

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
    }

    /**
     * POST /users/{id}/toggle-active — Aktifkan / Nonaktifkan akun
     */
    public function toggleActive(Request $request, $id)
    {
        $actor  = $request->user();
        $target = User::findOrFail($id);

        if (!$this->policy->toggleActive($actor, $target)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berwenang mengubah status akun ini.'], 403);
        }

        $target->update(['is_active' => !$target->is_active]);
        $action = $target->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->audit->log([
            'module'      => 'user_auth',
            'record_id'   => $target->id,
            'user_id'     => $actor->id,
            'role'        => $actor->role,
            'action'      => $target->is_active ? 'ACTIVATE_USER' : 'DEACTIVATE_USER',
            'description' => "{$actor->name} {$action} akun: {$target->name} ({$target->role}).",
            'new_values'  => ['is_active' => $target->is_active],
        ], $request);

        return response()->json([
            'success' => true,
            'message' => "Akun {$target->name} berhasil {$action}.",
            'data'    => $target,
        ]);
    }

    /**
     * GET /users/available-roles — Daftar role yang bisa dibuat oleh actor
     */
    public function availableRoles(Request $request)
    {
        $actor = $request->user();

        $allRoles = [
            'superadmin'        => 'Super Administrator',
            'kepsek'            => 'Kepala Sekolah',
            'dept_ygpkb'        => 'Departemen YGPKB',
            'asp_wilayah'       => 'ASP Wilayah',
            'kd_sarpras'        => 'Kepala Divisi Sarpras',
            'admin_sarpras'     => 'Admin Sarpras',
            'koordinator_ski'   => 'Koordinator SKI',
            'koordinator_utama' => 'Koordinator Utama',
        ];

        $filtered = collect($allRoles)->filter(
            fn($label, $role) => $this->policy->create($actor, $role)
        );

        return response()->json(['success' => true, 'data' => $filtered]);
    }
}
