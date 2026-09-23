<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeArea;
use App\Models\OfficeBranch;
use Illuminate\Http\Request;

class OfficeAreaController extends Controller
{
    public function index()
    {
        $areas = OfficeArea::with('officeBranch')->orderBy('radius_meters')->get();

        return view('admin.officeAreas.index', [
            'areas' => $areas,
            'mapAreas' => $areas->map(fn (OfficeArea $area) => [
                'id' => $area->id,
                'name' => $area->name,
                'latitude' => $area->latitude,
                'longitude' => $area->longitude,
                'radius_meters' => $area->radius_meters,
                'color' => $area->color,
            ])->values(),
            'branches' => OfficeBranch::orderBy('branch_name')->get(),
            'mapsKey' => config('services.google_maps.key'),
        ]);
    }

    public function store(Request $request)
    {
        OfficeArea::create($this->validated($request));
        return back()->with('success', 'Office area added successfully.');
    }

    public function update(Request $request, OfficeArea $officeArea)
    {
        $officeArea->update($this->validated($request));
        return back()->with('success', 'Office area updated successfully.');
    }

    public function destroy(OfficeArea $officeArea)
    {
        $officeArea->delete();
        return back()->with('success', 'Office area removed successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'office_branch_id' => 'nullable|exists:office_branches,id',
            'name' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:1|max:100000',
            'review_minutes' => 'required|integer|min:1|max:1440',
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
