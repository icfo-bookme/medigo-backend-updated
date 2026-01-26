<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Setting\Entities\SearchText;

class SearchTextController extends BaseController
{
    public function __construct(SearchText $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if (permission('showroom-access')) {
            $this->setPageData('Search Text', 'Search Text', 'fas fa-search', [['name' => 'Search Text']]);
            return view('setting::search-text.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('showroom-access')) {

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('showroom-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }

                $row = [];
                $row[] = $no;
                $row[] = $value->search_text;
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(Request $request)
    {
        if ($request->ajax() && permission('showroom-add')) {
            $validator = $request->validate([
                'search_text' => 'required|string',
            ]);
            $collection = collect($validator);
            $collection = $this->track_data($collection, $request->update_id);
            $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
            $output = $this->store_message($result, $request->update_id);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax() && permission('showroom-edit')) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
