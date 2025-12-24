<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Models\TrackMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Gate;
use Illuminate\Support\Facades\DB;

class TrackMemberController extends Controller
{
    use CsvImportTrait;

public function index(Request $request)
{
    abort_if(Gate::denies('track_member_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $user = Auth::user();
    $isAdmin = $user->roles->contains('title', 'Admin');

    $selectedUserId = $request->get('user_id');

    $trackMembers = \App\Models\TrackMember::with(['user'])
        ->when(!$isAdmin, function ($query) use ($user) {
            // Normal user -> apna data
            $query->where('user_id', $user->id);
        })
        ->when($isAdmin && $selectedUserId, function ($query) use ($selectedUserId) {
            // Admin -> filter by selected user
            $query->where('user_id', $selectedUserId);
        })
        ->orderBy('id', 'desc')
        ->get();

    // Dropdown users
    $createdUsers = \App\Models\User::query()
        ->when(!$isAdmin, function ($query) use ($user) {
            // Normal user -> sirf apna hi user
            $query->where('id', $user->id);
        })
        ->when($isAdmin, function ($query) {
            // Admin -> sab users
            $query->orderBy('name', 'asc');
        })
        ->get();

    return view('admin.trackMembers.index', compact('trackMembers', 'createdUsers', 'selectedUserId'));
}



    /**
     * Save location sent from browser (every 5 seconds)
     */
 public function trackLocation(Request $request)
{
    $loginUser = Auth::user();

    // Validate input
    $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'location' => 'nullable|string',
    ]);

    // Last saved location for this user
    $lastLocation = \App\Models\TrackMember::where('user_id', $loginUser->id)
        ->latest('id')
        ->first();

    $newLat = $request->latitude;
    $newLng = $request->longitude;

    // If last location exists, calculate distance
    if ($lastLocation) {
        $distance = $this->calculateDistance(
            $lastLocation->latitude,
            $lastLocation->longitude,
            $newLat,
            $newLng
        );

        // Save only if moved more than 16 meters
        if ($distance < 16) {
            return response()->json([
                'status' => 'ignored',
                'message' => 'User did not move beyond 16m',
                'distance_m' => round($distance, 2)
            ]);
        }
    }

    // Prepare data
    $data = [
        'user_id'  => $loginUser->id,
        'latitude' => $newLat,
        'longitude'=> $newLng,
        'location' => $request->location ?? '',
        'time'     => now(),
    ];

    // Save location
    \App\Models\TrackMember::create($data);

    return response()->json([
        'status' => 'Location saved',
        'distance_m' => isset($distance) ? round($distance, 2) : 0
    ]);
}

/**
 * Calculate distance between two coordinates in meters
 */
private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000; // meters

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $earthRadius * $c; // distance in meters
}


    /**
     * Return latest location of a selected user for map marker
     */
   public function getLatestLocation(Request $request)
{
    $user = auth()->user();

    // Sirf admin ko access
    if ($user->is_admin != 1) {
        return response()->json(['error' => 'Access denied.'], 403);
    }

    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json(['error' => 'User ID is required.'], 400);
    }

    $latest = TrackMember::where('user_id', $userId)
        ->latest('created_at')
        ->first();

    if (!$latest) {
        return response()->json(['error' => 'No location data found.'], 404);
    }

    return response()->json([
        'latitude'   => $latest->latitude,
        'longitude'  => $latest->longitude,
        'location'   => $latest->location,
        'created_at' => $latest->created_at->toDateTimeString(),
    ]);
}

/**
 * Return full location history of a selected user for DataTable
 */
public function getUserTrackData(Request $request)
{
    $user = auth()->user();

    // Sirf admin ko access
    if ($user->is_admin != 1) {
        return response()->json([]); // normal user ke liye blank data
    }

    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json([]);
    }

    $trackData = TrackMember::with('user')
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->take(50)
        ->get();

    return response()->json($trackData);
}


}
