<?php

namespace App\Http\Requests;

use App\Models\MakeCustomer;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyMakeCustomerRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('make_customer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:make_customers,id',
        ];
    }
}