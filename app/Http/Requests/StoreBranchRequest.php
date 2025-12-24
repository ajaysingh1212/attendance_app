<?php

namespace App\Http\Requests;

use App\Models\Branch;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreBranchRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('branch_create');
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'nullable',
            ],
            'address' => [
                'string',
                'nullable',
            ],
            '	latitude' => [
                'numeric',
                'nullable',
            ],
            'longitude' => [
                'numeric',
                'nullable',
            ],
            'pincode' => [
               
                'nullable',
            ],
            'state' => [
                'string',
                'nullable',
            ],
            'city' => [
                'string',
                'nullable',
            ],
            'legal_name' => [
                'string',
                'nullable',
            ],
            'gst' => [
                'string',
                'nullable',
            ],
            'pan' => [
                'string',
                'nullable',
            ],
            'incharge_name'=> [
                'string',
                'nullable',
            ]
        ];
    }
}
