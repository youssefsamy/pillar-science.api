<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class ConsumeInvitationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'password' => 'required'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
