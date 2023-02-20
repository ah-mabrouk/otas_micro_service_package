<?php

namespace Solutionplus\MicroService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Solutionplus\MicroService\Models\PermissionGroup;

class PermissionGroupStoreRequest extends FormRequest
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
        return [
            'name' => 'required|string|min:2|max:191|unique:permission_group_translations,name',
        ];
    }

    public function storePermissionGroup()
    {
        $currentTranslationNamespace = config('translatable.translation_models_path');
        config(['translatable.translation_models_path' => 'Solutionplus\MicroService\Models']);
        $this->permissionGroup = PermissionGroup::create([]);
        config(['translatable.translation_models_path' => $currentTranslationNamespace]);
        return $this->permissionGroup->refresh();
    }
}
