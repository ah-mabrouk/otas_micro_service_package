<?php

namespace Solutionplus\MicroService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Solutionplus\MicroService\Helpers\MsHttp;

class MicroServiceMapStoreRequest extends FormRequest
{
    public $microServiceMap;

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
            'name' => 'required|string|alpha_dash:ascii|min:3|max:190|unique:micro_service_maps,name',
            'origin' => 'required|string|min:6|max:190',
            'secret' => 'required|string|alpha_num:ascii|size:16|unique:micro_service_maps,destination_key',
        ];
    }

    public function storeMicroServiceMap()
    {
        return MsHttp::saveMicroservice($this->name, $this->origin, $this->secret);
    }
}
