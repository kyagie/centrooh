<?php

namespace App\Services\Billboards;

use App\Models\AgentDistrict;
use App\Models\Billboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillboardAssignmentService
{
    /**
     * Assign all billboards in a district to an agent.
     *
     * @param AgentDistrict $agentDistrict The agent-district relationship object
     * @return array Array containing count of assigned billboards and any errors
     */
    public function assignBillboardsInDistrict(AgentDistrict $agentDistrict): array
    {
        $agent = $agentDistrict->agent;
        $district = $agentDistrict->district;

        if (!$agent || !$district) {
            return [
                'success' => false,
                'message' => 'Agent or district not found',
                'assigned_count' => 0
            ];
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Get all billboards in the district
            $billboards = Billboard::where('district_id', $district->id)
                ->where('is_active', true)
                ->get();

            if ($billboards->isEmpty()) {
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'No active billboards found in the district',
                    'assigned_count' => 0
                ];
            }

            // Get existing billboard assignments to avoid duplicates
            $existingAssignments = $agent->billboards()
                ->wherePivotIn('billboard_id', $billboards->pluck('id'))
                ->pluck('billboards.id')
                ->toArray();

            // Filter out billboards that are already assigned
            $newBillboards = $billboards->whereNotIn('id', $existingAssignments);

            // Attach the new billboards to the agent
            if ($newBillboards->isNotEmpty()) {
                $agent->billboards()->attach($newBillboards->pluck('id')->toArray());
            }

            DB::commit();

            return [
                'success' => true,
                'message' => count($newBillboards) . ' billboards assigned to agent successfully',
                'assigned_count' => count($newBillboards),
                'total_billboards' => count($billboards),
                'already_assigned' => count($existingAssignments)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning billboards to agent: ' . $e->getMessage(), [
                'agent_id' => $agent->id,
                'district_id' => $district->id,
                'exception' => $e
            ]);

            return [
                'success' => false,
                'message' => 'Error assigning billboards: ' . $e->getMessage(),
                'assigned_count' => 0
            ];
        }
    }
}
