<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Room;
use App\Models\Vendor;
use App\Models\DocumentSignatory;

class MasterDataController extends Controller
{
    // ────────────────────────────── UNITS ──────────────────────────────

    public function getUnits(Request $request)
    {
        $query = Unit::query();
        if ($request->has('category'))      $query->where('category', $request->category);
        if ($request->has('asp_territory')) $query->where('asp_territory', $request->asp_territory);
        if ($request->has('search')) {
            $q = $request->search;
            $query->where(function($qb) use ($q) {
                $qb->where('name', 'like', '%'.$q.'%')
                   ->orWhere('unit_code', 'like', '%'.$q.'%');
            });
        }
        return response()->json(['success' => true, 'data' => $query->orderBy('name')->get()]);
    }

    public function storeUnit(Request $request)
    {
        $validated = $request->validate([
            'unit_code'     => 'required|string|max:30|unique:units,unit_code',
            'name'          => 'required|string|max:100',
            'category'      => 'required|string|max:60',
            'asp_territory' => 'nullable|string|max:60',
        ]);
        $unit = Unit::create($validated);
        return response()->json(['success' => true, 'message' => 'Unit berhasil ditambahkan', 'data' => $unit], 201);
    }

    public function updateUnit(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $validated = $request->validate([
            'unit_code'     => 'required|string|max:30|unique:units,unit_code,'.$id,
            'name'          => 'required|string|max:100',
            'category'      => 'required|string|max:60',
            'asp_territory' => 'nullable|string|max:60',
        ]);
        $unit->update($validated);
        return response()->json(['success' => true, 'message' => 'Unit berhasil diperbarui', 'data' => $unit]);
    }

    public function deleteUnit($id)
    {
        $unit = Unit::findOrFail($id);
        if ($unit->users()->exists()) {
            return response()->json(['success' => false, 'message' => 'Unit tidak bisa dihapus karena masih memiliki pengguna terdaftar.'], 422);
        }
        $unit->delete();
        return response()->json(['success' => true, 'message' => 'Unit berhasil dihapus']);
    }

    // ────────────────────────────── ROOMS ──────────────────────────────

    public function getRoomsByUnit($id)
    {
        $rooms = Room::where('unit_id', $id)->get();
        return response()->json(['success' => true, 'data' => $rooms]);
    }

    public function addRoom(Request $request)
    {
        $validated = $request->validate([
            'unit_id'     => 'required|exists:units,id',
            'name'        => 'required|string|max:120',
            'floor_level' => 'nullable|string|max:20',
        ]);
        $room = Room::create($validated);
        return response()->json(['success' => true, 'message' => 'Room berhasil ditambahkan', 'data' => $room], 201);
    }

    // ────────────────────────────── VENDORS ──────────────────────────────

    public function getVendors(Request $request)
    {
        $query = Vendor::query();
        if ($request->has('search') && $request->search !== '') {
            $q = $request->search;
            $query->where(function($qb) use ($q) {
                $qb->where('name', 'like', '%'.$q.'%')
                   ->orWhere('pic_name', 'like', '%'.$q.'%');
            });
        }
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        return response()->json(['success' => true, 'data' => $query->orderBy('name')->get()]);
    }

    public function addVendor(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'pic_name'  => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:40',
            'email'     => 'nullable|email|max:100',
            'address'   => 'nullable|string',
            'specialty' => 'nullable|string|max:100',
        ]);
        $validated['is_active'] = true;
        $vendor = Vendor::create($validated);
        return response()->json(['success' => true, 'message' => 'Vendor berhasil ditambahkan', 'data' => $vendor], 201);
    }

    public function updateVendor(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'pic_name'  => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:40',
            'email'     => 'nullable|email|max:100',
            'address'   => 'nullable|string',
            'specialty' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);
        $vendor->update($validated);
        return response()->json(['success' => true, 'message' => 'Vendor berhasil diperbarui', 'data' => $vendor]);
    }

    public function toggleVendorActive($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update(['is_active' => !$vendor->is_active]);
        $label = $vendor->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return response()->json(['success' => true, 'message' => "Vendor berhasil $label", 'data' => $vendor]);
    }

    public function deleteVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
        return response()->json(['success' => true, 'message' => 'Vendor berhasil dihapus']);
    }

    public function getVendorHistory($id)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    // ────────────────────────────── SIGNATORIES ──────────────────────────────

    public function getSignatories()
    {
        return response()->json(['success' => true, 'data' => DocumentSignatory::active()->get()]);
    }

    public function updateSignatory(Request $request, $id)
    {
        $sig = DocumentSignatory::findOrFail($id);
        $sig->update($request->only(['default_signer_name', 'default_signer_role']));
        return response()->json(['success' => true, 'message' => 'Signatory berhasil diperbarui', 'data' => $sig]);
    }
}
