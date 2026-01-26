<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HRM\Entities\WorkingDay;

class WorkingDayController extends BaseController
{
    public function __construct(WorkingDay $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('workingday-access')){
            $setTitle = __('file.working days');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $working_days = WorkingDay::get()->toArray();
            $deletable = self::DELETABLE;
            return view('hrm::working-day.index',compact('deletable','working_days'));
        }else{
            return $this->access_blocked();
        }
    }

    public function update(Request $request) {
        $id = 1;
        for ($i = 0; $i < count($request->day); $i++) {
            $affected_row = WorkingDay::where('id', $id++)
                ->update(['working_status' => $request->day[$i]]);
        }
        return redirect()->back()->with('message', 'Update successfully.');
    }
}
