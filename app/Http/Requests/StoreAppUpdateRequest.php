<?php

namespace App\Http\Requests;

use App\Models\AppUpdate;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreAppUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('app_update_create');
    }

    public function rules()
    {
        return [
            'version' => [
                'string',
                'required',
            ],
            'heading' => [
                'string',
                'required',
            ],
            'content' => [
                'required',
            ],
            'app_file' => [
                'required|file|mimetypes:application/vnd.android.package-archive|max:102400',
            ],
            
        ];
    }
}
