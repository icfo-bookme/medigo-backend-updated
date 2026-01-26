<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignProduct;
use Symfony\Component\HttpFoundation\Response;

class CampaignApiController extends Controller
{
    public function getCampaign()
    {
        $currentDate = now();
        $campaigns = Campaign::where('status', 1)
            ->whereDate('start_date', '<=', $currentDate)  // Check if start_date is in the past or today
            ->whereDate('end_date', '>=', $currentDate)    // Check if end_date is in the future or today
            ->get();
        if ($campaigns->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No campaign found'], Response::HTTP_NOT_FOUND);
        } else {
            return response()->json(['success' => true, 'data' => $campaigns], Response::HTTP_OK);
        }
    }
}
