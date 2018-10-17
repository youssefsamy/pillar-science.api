<?php


namespace App\Api\V1\Requests;

use App\Models\RemoteDirectory;
use Dingo\Api\Http\FormRequest;

class DesktopClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    /**
     * @return RemoteDirectory|null
     */
    public function getRemoteDirectory()
    {
        if ($this->has('secret_key')) {
            $token = $this->get('secret_key');

            return RemoteDirectory::whereSecretKey($token)->firstOrFail();
        }

        return null;
    }
}