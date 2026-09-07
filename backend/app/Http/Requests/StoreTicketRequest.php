<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Role check handled by route middleware
    }

    public function rules(): array
    {
        $base = [
            'procedure_type'  => 'required|in:perbaikan,pemeliharaan,pengadaan,mutasi,pemutihan',
            'urgency_level'   => 'required|in:rendah,sedang,tinggi_darurat',
            'title'           => 'required|string|max:200',
            'photos.*'        => 'nullable|file|image|max:5120',
        ];

        $type = $this->input('procedure_type');

        if ($type === 'perbaikan') {
            return array_merge($base, [
                'unit_id'                => 'required|exists:units,id',
                'location_name'          => 'nullable|string|max:150',
                'specific_location'      => 'nullable|string|max:255',
                'problem_cause'          => 'required|string|max:2000',
                'target_object_category' => 'required|in:fasilitas_ruangan,barang_inventaris',
                'item_name'              => 'nullable|string|max:150',
                'inventory_code'         => 'nullable|string|max:80',
                'estimated_cost'         => 'nullable|numeric|min:0',
            ]);
        }

        if ($type === 'pengadaan') {
            return array_merge($base, [
                'unit_id'        => 'required|exists:units,id',
                'location_name'  => 'nullable|string|max:150',
                'problem_cause'  => 'required|string|max:2000',
                'items'          => 'required|array|min:1',
                'items.*.item_description'    => 'required|string|max:255',
                'items.*.quantity'            => 'required|integer|min:1',
                'items.*.unit_metric'         => 'required|string|max:30',
                'items.*.estimated_unit_price'=> 'required|numeric|min:0',
                'items.*.estimated_subtotal'  => 'nullable|numeric|min:0',
                'items.*.item_notes'          => 'nullable|string|max:255',
            ]);
        }

        if ($type === 'mutasi') {
            return array_merge($base, [
                'unit_id'               => 'required|exists:units,id',
                'location_name'         => 'nullable|string|max:150',
                'target_unit_id'        => 'required|exists:units,id|different:unit_id',
                'target_location_name'  => 'nullable|string|max:150',
                'problem_cause'         => 'required|string|max:2000',
                'items'                 => 'required|array|min:1',
                'items.*.item_description' => 'required|string|max:255',
                'items.*.quantity'         => 'required|integer|min:1',
                'items.*.condition_status' => 'nullable|in:baik,rusak_ringan,rusak_berat,afkir',
                'items.*.inventory_code'   => 'nullable|string|max:80',
                'items.*.item_notes'       => 'nullable|string|max:255',
            ]);
        }

        if ($type === 'pemutihan') {
            return array_merge($base, [
                'unit_id'                         => 'required|exists:units,id',
                'location_name'                   => 'nullable|string|max:150',
                'problem_cause'                   => 'required|string|max:2000',
                'disposal_action'                 => 'required|in:dimusnahkan,dijual,dialihkan_disumbangkan',
                'disposal_destination_or_notes'   => 'nullable|string|max:1000',
                'estimated_salvage_value'         => 'nullable|numeric|min:0',
            ]);
        }

        return $base;
    }

    public function messages(): array
    {
        return [
            'procedure_type.required' => 'Kategori prosedur wajib dipilih.',
            'unit_id.required'        => 'Unit / satuan kerja wajib dipilih.',
            'unit_id.exists'          => 'Unit yang dipilih tidak valid.',
            'location_name.max'       => 'Nama ruangan/lokasi maksimal 150 karakter.',
            'title.required'          => 'Judul tiket wajib diisi.',
            'title.max'               => 'Judul tiket maksimal 200 karakter.',
            'problem_cause.required'  => 'Deskripsi / uraian masalah wajib diisi.',
            'urgency_level.required'  => 'Tingkat urgensi wajib dipilih.',
            'items.required'          => 'Daftar item wajib diisi (minimal 1 baris).',
            'items.*.item_description.required'     => 'Nama barang pada baris ke-:position wajib diisi.',
            'items.*.quantity.required'             => 'Jumlah pada baris ke-:position wajib diisi.',
            'items.*.estimated_unit_price.required' => 'Harga satuan pada baris ke-:position wajib diisi.',
            'disposal_action.required'              => 'Tindakan disposal wajib dipilih.',
            'target_unit_id.required'               => 'Unit tujuan mutasi wajib dipilih.',
            'target_unit_id.different'              => 'Unit tujuan tidak boleh sama dengan unit asal.',
            'photos.*.image'                        => 'File lampiran harus berupa gambar (JPG, PNG, WebP).',
            'photos.*.max'                          => 'Ukuran setiap foto maksimal 5 MB.',
        ];
    }

    /**
     * Override to return JSON 422 error instead of redirect
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal. Periksa kembali isian form.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
