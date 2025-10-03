<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransaccionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'user'    => $this->user ? [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'monto'   => $this->monto,
            'fecha'   => $this->fecha,
            'created' => $this->created_at,
            'updated' => $this->updated_at,
        ];
    }
}
