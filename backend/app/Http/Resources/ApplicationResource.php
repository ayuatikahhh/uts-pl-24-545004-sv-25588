<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Application
 */
class ApplicationResource extends JsonResource
{
    /**
     * Bentuk JSON sesuai API contract (dibungkus "data" otomatis oleh Laravel).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company' => $this->company,
            'position' => $this->position,
            'status' => $this->status,
            'deadline' => $this->deadline?->format('Y-m-d'),
        ];
    }
}