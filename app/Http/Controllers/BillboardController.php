<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AgentBillboardService;

class BillboardController extends Controller
{
    /**
     * Get billboard coordinates for an agent (cached).
     */
    public function getAgentBillboardCoordinates(Request $request)
    {
        $service = new AgentBillboardService();
        $agentId = $request->user()->agent->id;
        $coordinates = $service->getBillboardCoordinates($agentId);

        return response()->json([
            'status' => 'success',
            'coordinates' => $coordinates,
        ]);
    }

    //Single billboard details
    public function getBillboardDetails(Request $request, $billboardId)
    {
        $service = new AgentBillboardService();
        $agentId = $request->user()->agent->id;
        $billboardDetails = $service->getBillboardDetails($agentId, $billboardId);

        if (!$billboardDetails) {
            return response()->json([
                'status' => 'error',
                'message' => 'Billboard not found or not assigned to the agent.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'billboard' => $billboardDetails,
        ]);
    }
}
