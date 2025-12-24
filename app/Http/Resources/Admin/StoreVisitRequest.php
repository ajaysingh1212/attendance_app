<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreVisitRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('visit_create');
    }

    public function rules()
    {
        return [
            'user' => [
                'string',
                'nullable',
            ],
            'latitude' => [
                'string',
                'nullable',
            ],
            'longitude' => [
                'string',
                'nullable',
            ],
            'location' => [
                'string',
                'nullable',
            ],
            'visited_time' => [
                'string',
                'nullable',
            ],
            'visited_out_latitude' => [
                'string',
                'nullable',
            ],
            'visited_out_longitude' => [
                'string',
                'nullable',
            ],
            'visited_out_location' => [
                'string',
                'nullable',
            ],
            'visited_out_time' => [
                'string',
                'nullable',
            ],
            'visited_duration' => [
                'string',
                'nullable',
            ],
        ];
    }
}
