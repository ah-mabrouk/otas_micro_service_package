<?php

namespace Solutionplus\MicroService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Solutionplus\MicroService\Models\MicroServiceMap;

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
            'secret' => 'required|string|alpha_num:ascii|size:16',
        ];
    }

    public function storeMicroServiceMap()
    {
        $this->microServiceMap = MicroServiceMap::create([
            'name' => $this->name,
            'display_name' => \ucfirst(\str_replace(['_', '-'], ' ', $this->name)),
            'origin' => $this->origin,
            'destination_key' => $this->secret,
        ]);
        return $this->microServiceMap->refresh();
    }
}
