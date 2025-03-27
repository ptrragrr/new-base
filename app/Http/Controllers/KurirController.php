<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class KurirController extends Controller
{
    /**
     * Get paginated list of kurir
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Kurir::when($request->search, function (Builder $query, string $search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('password', 'like', "%$search%")
                ->orWhere('rating', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    /**
     * Store a newly created kurir
     */
    public function store(StoreKurirRequest $request)
    {
        $validatedData = $request->validated();

        // Enkripsi password sebelum disimpan
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Default rating jika tidak dikirim
        if (!isset($validatedData['rating'])) {
            $validatedData['rating'] = 5;
        }

        // Simpan foto jika ada
        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        // Simpan data kurir ke database
        $kurir = Kurir::create($validatedData);

        return response()->json([
            'success' => true,
            'kurir' => $kurir
        ]);
    }

    /**
     * Show a specific kurir
     */
    public function show(Kurir $kurir)
    {
        return response()->json([
            'kurir' => $kurir
        ]);
    }

    /**
     * Update an existing kurir
     */
    public function update(UpdateKurirRequest $request, Kurir $kurir)
    {
        $validatedData = $request->validated();

        // Cek apakah password dikirim, jika ya, enkripsi sebelum menyimpan
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Jika tidak diisi, jangan update password
        }

        // Update rating jika dikirim
        if ($request->filled('rating')) {
            $validatedData['rating'] = max(1, min(5, $validatedData['rating']));
        }

        // Simpan foto jika ada, dan hapus yang lama jika diperbarui
        if ($request->hasFile('photo')) {
            if ($kurir->photo) {
                Storage::disk('public')->delete($kurir->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        } else {
            if ($kurir->photo && !$request->filled('photo')) {
                Storage::disk('public')->delete($kurir->photo);
                $validatedData['photo'] = null;
            }
        }

        // Update data kurir di database
        $kurir->update($validatedData);

        return response()->json([
            'success' => true,
            'kurir' => $kurir
        ]);
    }
    public function get()
{
    return response()->json([
        'success' => true,
        'data' => Kurir::all() // Atau Role::all() jika itu yang diinginkan
    ]);
}


    /**
     * Delete a kurir
     */
    public function destroy(Kurir $kurir)
    {
        if ($kurir->photo) {
            Storage::disk('public')->delete($kurir->photo);
        }

        $kurir->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
