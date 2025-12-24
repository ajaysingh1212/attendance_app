<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Http\Resources\Admin\VisitResource;
use App\Models\Visit;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VisitApiController extends Controller
{
    use MediaUploadingTrait;

    // ✅ Admin: list all visits
    public function index()
    {
        abort_if(Gate::denies('visit_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return VisitResource::collection(Visit::with('user')->get());
    }

    // ✅ Admin: store visit
    public function store(StoreVisitRequest $request)
    {
        $visit = Visit::create($request->all());
        $this->handleImages($request, $visit);

        return (new VisitResource($visit->load('user')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    // ✅ Admin: show visit
    public function show(Visit $visit)
    {
        abort_if(Gate::denies('visit_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new VisitResource($visit->load('user'));
    }

    // ✅ Admin: update visit
    public function update(UpdateVisitRequest $request, Visit $visit)
    {
        $visit->update($request->all());
        $this->handleImages($request, $visit);

        return (new VisitResource($visit->load('user')))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    // ✅ Admin: delete visit
    public function destroy(Visit $visit)
    {
        abort_if(Gate::denies('visit_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $visit->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    // ✅ Public API: submit visit for any user
public function submitVisit(Request $request)
{
    // Validation: सिर्फ user_id required
    $validated = $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'title'   => 'nullable|string|max:255', // ✅ नया field
    ]);

    // Map user_id → db column "user" और बाकी fields optional
    $data = [
        'user' => $validated['user_id'],
        'title' => $request->input('title'), // ✅ save title
        'latitude' => $request->input('latitude'),
        'longitude' => $request->input('longitude'),
        'location' => $request->input('location'),
        'visited_time' => $request->input('visited_time'),
        'visited_out_latitude' => $request->input('visited_out_latitude'),
        'visited_out_longitude' => $request->input('visited_out_longitude'),
        'visited_out_location' => $request->input('visited_out_location'),
        'visited_out_time' => $request->input('visited_out_time'),
        'visited_duration' => $request->input('visited_duration'),
    ];

    // Visit create
    $visit = Visit::create($data);

    // Handle images (multipart/form-data)
    $imageFields = [
        'visited_counter_image',
        'visit_self_image',
        'visited_out_counter_image',
        'visited_out_self_image'
    ];

    foreach ($imageFields as $field) {
        if ($request->hasFile($field)) {
            $visit->addMedia($request->file($field))
                  ->toMediaCollection($field);
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Visit submitted successfully',
        'data' => new \App\Http\Resources\Admin\VisitResource($visit->load('user')),
    ]);
}


   public function getVisitsByUser($userId)
{
    // Check user exist करता है या नहीं
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
            'data' => []
        ], 404);
    }

    // Visits निकालना (क्योंकि column = user)
    $visits = \App\Models\Visit::where('user', $userId)
                ->latest()
                ->get();

    return response()->json([
        'status' => true,
        'message' => 'Visits fetched successfully',
        'data' => \App\Http\Resources\Admin\VisitResource::collection($visits)
    ]);
}





}
