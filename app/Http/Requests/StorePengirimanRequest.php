<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengirimanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan ini 'true' agar request bisa diproses
    }
public function rules()
{
    return [
        'kurir_id' => 'required|exists:kurir,id',
        'paket' => 'required|string',
        'status' => 'required|string|in:dikemas,dikirim,diterima',
        'tanggal_pengiriman' => 'required|date',
        'biaya' => 'required|numeric',
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
            'biaya.required'
        ];
    }
}