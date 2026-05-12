<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryProblemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->problemId,
            'title' => $this->title,
            'difficulty' => ucfirst($this->difficulty),
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d'),
            'creator' => [
                'username' => $this->creator->username ?? 'Unknown',
            ],
            // We can add logic later to show the user's specific best score or language used
        ];
    }
}
