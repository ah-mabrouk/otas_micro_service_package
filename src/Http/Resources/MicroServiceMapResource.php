<?php

namespace Solutionplus\MicroService\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MicroServiceMapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'display_name' => $this->display_name,
            'name' => $this->name,

            'origin' => $this->origin,

            'source_key' => $this->source_key,
            'destination_key' => $this->destination_key,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
