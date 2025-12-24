<?php

namespace App\Http\Requests;

use App\Models\MakeCustomer;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreMakeCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('make_customer_create');
    }

    public function rules()
    {
        return [
            'customer_code' => [
                'string',
                'min:10',
                'max:10',
                'required',
            ],
            'shop_name' => [
                'string',
                'required',
            ],
            'owner_name' => [
                'string',
                'required',
            ],
            'phone_number' => [
                'string',
                'required',
            ],
            'email' => [
                'string',
                'required',
            ],
            'pincode' => [
                'string',
                'nullable',
            ],
            'area' => [
                'string',
                'nullable',
            ],
            'city' => [
                'string',
                'nullable',
            ],
            'state' => [
                'string',
                'nullable',
            ],
            'country' => [
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
            'gst_number' => [
                'string',
                'min:15',
                'max:15',
                'nullable',
            ],
            'license_no' => [
                'string',
                'nullable',
            ],
            'payment_terms' => [
                'string',
                'nullable',
            ],
            'bank_name' => [
                'string',
                'nullable',
            ],
            'ifsc_code' => [
                'string',
                'nullable',
            ],
            'account_no' => [
                'string',
                'nullable',
            ],
        ];
    }
}