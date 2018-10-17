<?php

namespace App\Api\V1\Requests;

use App\Models\Dataset;
use Dingo\Api\Http\FormRequest;
use Illuminate\Validation\Validator;

class DatasetCreateMappingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    public function withValidator(Validator $validator)
    {
        $targetParent = $this->route('target');
        $source = $this->route('dataset');

        $validator->after(function (Validator $validator) use ($targetParent, $source) {
            $exists = Dataset::query()
                ->where('parent_id', $targetParent->id)
                ->where('mapped_dataset_id', $source->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('parent_id', 'This dataset is already mapped to this destination. Cannot map twice.');
            }
        });
    }
}
