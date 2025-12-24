<?php

namespace App\Http\Requests;

use App\Models\AddRequestAmount;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreAddRequestAmountRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('add_request_amount_create');
    }

    public function rules()
    {
        return [
            'amount' => [
                'string',
                'nullable',
            ],
            'description' => [
                'string',
                'nullable',
            ],
            'remark' => [
                'string',
                'nullable',
            ],
        ];
    }
}
