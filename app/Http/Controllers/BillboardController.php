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
}
