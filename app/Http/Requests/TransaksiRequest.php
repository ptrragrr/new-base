<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransaksiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'nama_kasir' => 'required|string',
        'metode_pembayaran' => 'required|string',
        'keranjang' => 'required|array|min:1',
        'keranjang.*.id_barang' => 'required|integer|exists:barang,id',
        'keranjang.*.jumlah' => 'required|integer|min:1',
        'keranjang.*.total_harga' => 'required|numeric|min:0',
        'total' => 'required|numeric|min:0',
    ];
}
}
