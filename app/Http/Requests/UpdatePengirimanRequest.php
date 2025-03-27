<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengirimanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'kurir_id' => 'required|exists:kurirs,id',
            'paket' => 'required|string|max:255',
            'status' => 'required|in:pending,dikirim,selesai', // Hanya boleh salah satu
            'tanggal_pengiriman' => 'required|date',
            'biaya' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'kurir_id.required' => 'Kurir harus dipilih.',
            'kurir_id.exists' => 'Kurir tidak valid.',
            'paket.required' => 'Nama paket harus diisi.',
            'paket.max' => 'Nama paket maksimal 255 karakter.',
            'status.required' => 'Status pengiriman harus diisi.',
            'status.in' => 'Status harus "pending", "dikirim", atau "selesai".',
            'tanggal_pengiriman.required' => 'Tanggal pengiriman harus diisi.',
            'tanggal_pengiriman.date' => 'Format tanggal pengiriman tidak valid.',
            'biaya.required' => 'Biaya pengiriman harus diisi.',
            'biaya.numeric' => 'Biaya harus berupa angka.',
            'biaya.min' => 'Biaya tidak boleh negatif.',
        ];
    }
}
