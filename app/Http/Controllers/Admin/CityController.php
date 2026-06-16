<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::withCount('areas')
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%$s%")
                  ->orWhere('name_ar', 'like', "%$s%")
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.settings.cities.index', compact('cities'));
    }

    public function create()
    {
        return view('admin.settings.cities.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'name_ar'         => 'nullable|string|max:150',
            'is_active'       => 'nullable|boolean',
            'delivery_price'  => 'nullable|numeric|min:0',
            'areas'           => 'nullable|array',
            'areas.*.name'    => 'required|string|max:150',
            'areas.*.name_ar' => 'nullable|string|max:150',
        ]);

        $city = City::create([
            'name'           => $data['name'],
            'name_ar'        => $data['name_ar'] ?? null,
            'is_active'      => isset($data['is_active']),
            'delivery_price' => $data['delivery_price'] ?? 0,
        ]);

        foreach ($data['areas'] ?? [] as $area) {
            $city->areas()->create([
                'name'      => $area['name'],
                'name_ar'   => $area['name_ar'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.cities.index')
            ->with('success', 'City added successfully.');
    }

    public function edit(City $city)
    {
        $city->load('areas');
        return view('admin.settings.cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'name_ar'        => 'nullable|string|max:150',
            'is_active'      => 'nullable|boolean',
            'delivery_price' => 'nullable|numeric|min:0',
        ]);

        $city->update([
            'name'           => $data['name'],
            'name_ar'        => $data['name_ar'] ?? null,
            'is_active'      => isset($data['is_active']),
            'delivery_price' => $data['delivery_price'] ?? 0,
        ]);

        return redirect()->route('admin.cities.edit', $city)
            ->with('success', 'City updated successfully.');
    }

    public function toggle(City $city)
    {
        $city->update(['is_active' => !$city->is_active]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('admin.cities.index')
            ->with('success', 'City deleted.');
    }

    public function storeArea(Request $request, City $city)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
        ]);

        $city->areas()->create([
            'name'    => $data['name'],
            'name_ar' => $data['name_ar'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Area \"{$data['name']}\" added.");
    }

    public function destroyArea(City $city, Area $area)
    {
        $area->delete();
        return back()->with('success', 'Area deleted.');
    }
}
