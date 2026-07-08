<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class IdCardApiController extends Controller
{
    /**
     * Preview ID Card as PDF in browser
     */
    public function preview($id)
    {
        $user = User::with('employee')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $pdf = Pdf::loadView('idcard.template', [
            'user' => $user,
            'employee' => $user->employee
        ])->setPaper('a4', 'portrait');

        // Stream PDF in browser
        return $pdf->stream('idcard_'.$user->id.'.pdf');
    }

    /**
     * Download ID Card as PDF
     */
    public function download($id)
    {
        $user = User::with('employee')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $pdf = Pdf::loadView('idcard.template', [
            'user' => $user,
            'employee' => $user->employee
        ])->setPaper('a4', 'portrait');

        // Force download
        return $pdf->download('idcard_'.$user->id.'.pdf');
    }
}
