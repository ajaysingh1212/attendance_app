<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;

class DocumentApiController extends Controller
{
    public function downloadPolicy()
    {
        $filePath = public_path('terms/policy.pdf');

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'Policy PDF not found.'
            ], 404);
        }

        return response()->download(
            $filePath,
            'EemotClocking_Privacy_Policy.pdf',
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}