<?php

namespace App\Http\Controllers;

use App\Models\Kurir; // Huruf besar untuk model
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KurirController extends Controller
{
    /**
     * Get all kurir (optional filter by role_id)
     */
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Role::all()
        ]);
    }

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
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('photo', 'like', "%$search%")
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

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

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

        $kurir->update($validatedData);

        return response()->json([
            'success' => true,
            'name' => $kurir->name,
                    'email' => $kurir->email,
                    'phone' => $kurir->phone,
                    'photo' => $kurir->photo,
                    'status' => $kurir->status
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
