<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KategoriRequest extends FormRequest
{
    public function authorize()
    {
        return true; // penting agar request-nya tidak ditolak
    }

    public function rules()
    {
        return [
            'kategori_barang' => 'required|string|max:255'
        ];
    }
}
