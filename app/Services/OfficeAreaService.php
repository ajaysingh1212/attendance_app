<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OfficeArea;
use Illuminate\Support\Collection;

class OfficeAreaService
{
    public function areasFor(?Employee $employee): Collection
    {
        if ($employee && (
            strtolower(trim((string) $employee->branch_id)) === 'anywhere'
            || strtolower(trim((string) $employee->attendance_source)) === 'anywhere'
        )) {
            return collect();
        }

        $branchId = $employee?->branch_id;
        $officeBranchId = $employee?->office_branch_id;

        return OfficeArea::query()
            ->where('is_active', true)
            ->when($branchId || $officeBranchId, function ($query) use ($branchId, $officeBranchId) {
                $query->where(function ($q) use ($branchId, $officeBranchId) {
                    $q->where(function ($sub) use ($branchId) {
                        $sub->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });

                    if ($officeBranchId) {
                        $q->orWhere(function ($sub) use ($officeBranchId) {
                            $sub->whereNull('office_branch_id')->orWhere('office_branch_id', $officeBranchId);
                        });
                    }
                });
            })
            ->orderBy('radius_meters')
            ->get();
    }

    public function locate(?Employee $employee, float $latitude, float $longitude): array
    {
        $nearest = null;

        foreach ($this->areasFor($employee) as $area) {
            $distance = $this->distance($latitude, $longitude, $area->latitude, $area->longitude);
            if ($nearest === null || $distance < $nearest['distance']) {
                $nearest = ['area' => $area, 'distance' => $distance, 'inside' => $distance <= $area->radius_meters];
            }
            if ($distance <= $area->radius_meters) {
                return ['area' => $area, 'distance' => $distance, 'inside' => true];
            }
        }

        return $nearest ?? ['area' => null, 'distance' => null, 'inside' => false];
    }

    public function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }
}
