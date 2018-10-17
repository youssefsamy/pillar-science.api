<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class RemoteDirectoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'string|min:1',
            'computer_id' => 'string|min:1'
        ];
    }
}