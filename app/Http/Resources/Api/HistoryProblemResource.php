<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryProblemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->problemId,
            'title' => $this->title,
            'difficulty' => ucfirst((string) $this->difficulty),
            'status' => $this->status,
            'created_at' => optional($this->created_at)->format('Y-m-d'),
            'creator' => [
                'username' => $this->creator?->username ?? 'Unknown',
            ],
        ];
    }

    public static function paginatedProblemResponse(LengthAwarePaginator $problems): JsonResponse
    {
        $problems->getCollection()->loadMissing('creator');

        return response()->json([
            'data' => static::collection($problems->getCollection())->resolve(),
            'meta' => [
                'current_page' => $problems->currentPage(),
                'from' => $problems->firstItem(),
                'last_page' => $problems->lastPage(),
                'path' => $problems->path(),
                'per_page' => $problems->perPage(),
                'to' => $problems->lastItem(),
                'total' => $problems->total(),
            ],
            'links' => [
                'first' => $problems->url(1),
                'last' => $problems->url($problems->lastPage()),
                'prev' => $problems->previousPageUrl(),
                'next' => $problems->nextPageUrl(),
            ],
        ]);
    }
}
