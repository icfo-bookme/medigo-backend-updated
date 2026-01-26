<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Service\HomeService;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    protected $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    public function index()
    {
        if (permission('dashboard-access')) {
            $this->setPageData('Dashboard', 'Dashboard', 'fas fa-technometer');

            $start_date = strtotime(date('Y') . '-01-01');
            $end_date = strtotime(date('Y') . '-12-31');

            $showrooms = $this->homeService->getShowroomsData($start_date, $end_date);
            return view('home', compact('showrooms'));
        } else {
            if (permission('sale-access')) {
                return redirect('pos');
            } else {
                return redirect('unauthorized')->with(['status' => 'error', 'message' => 'Unauthorized Access Blocked']);
            }
        }
    }

    public function dashboard_data($start_date, $end_date)
    {
        $data = $this->homeService->getDashboardData($start_date, $end_date);
        return response()->json($data);
    }

    public function unauthorized()
    {
        $this->setPageData('Unauthorized', 'Unauthorized', 'fas fa-ban', [['name' => 'Unauthorized']]);
        return view('unauthorized');
    }

    public function product_stock_alert()
    {
        $data = $this->homeService->getProductStockAlert();
        return $data;
    }

    public function currentBalanceData()
    {
        $data = $this->homeService->getCurrentBalanceDetails();
        return view('balance.data', $data)->render();
    }
}
