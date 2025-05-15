<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\Cache;

class AgentBillboardService
{
    /**
     * Get billboard coordinates for an agent, cached.
     *
     * @param int $agentId
     * @return array
     */
    public function getBillboardCoordinates(int $agentId): array
    {
        $cacheKey = "agent:{$agentId}:billboard_coordinates";
        return Cache::remember($cacheKey, 3600, function () use ($agentId) {
            $agent = Agent::with(['billboards'])->find($agentId);
            if (!$agent) return [];
            return $agent->billboards->map(function ($billboard) {
                return [
                    'id' => $billboard->id,
                    'name' => $billboard->name,
                    'address' => $billboard->address,
                    'status' => $billboard->status,
                    'location' => $billboard->location,
                    'latitude' => $billboard->latitude,
                    'longitude' => $billboard->longitude,
                ];
            })->toArray();
        });
    }

    /**
     * Refresh the cached billboard coordinates for an agent.
     *
     * @param int $agentId
     * @return void
     */
    public function refreshBillboardCoordinates(int $agentId): void
    {
        $cacheKey = "agent:{$agentId}:billboard_coordinates";
        Cache::forget($cacheKey);
        $this->getBillboardCoordinates($agentId);
    }

    /**
     * Get details of a single billboard for an agent.
     *
     * @param int $agentId
     * @param int $billboardId
     * @return array|null
     */
    public function getBillboardDetails(int $agentId, int $billboardId): ?array
    {
        $agent = Agent::with(['billboards' => function ($query) use ($billboardId) {
            $query->where('id', $billboardId);
        }])->find($agentId);
        if (!$agent || $agent->billboards->isEmpty()) {
            return null;
        }
        $billboard = $agent->billboards->first();
        return [
            'id' => $billboard->id,
            'name' => $billboard->name,
            'address' => $billboard->address,
            'status' => $billboard->status,
            'location' => $billboard->location,
            'latitude' => $billboard->latitude,
            'longitude' => $billboard->longitude,
            'updated_at' => $billboard->updated_at,
        ];
    }
}
