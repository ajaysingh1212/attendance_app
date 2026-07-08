<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeApiController extends Controller
{
    // ✅ Get all leave types
    public function index()
    {
        $leaveTypes = LeaveType::all();

        return response()->json([
            'success' => true,
            'message' => 'Leave types fetched successfully',
            'data' => $leaveTypes
        ]);
    }
}
