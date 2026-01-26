<?php
namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Frontend\Entities\PromotionVideo;

class PromotionVideoController extends BaseController
{
    public function __construct(PromotionVideo $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('brand-access')) {
            $this->setPageData('Promotion Video', 'Promotion Video', 'fa-solid fa-video', [['name' => 'Promotion Video']]);
            return view('frontend::promotion-video.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            $this->set_datatable_default_properties($request); //set datatable default properties
            $list = $this->model->getDatatableList();          //get table data
            $data = [];
            $no   = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('brand-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->title . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                $url = $value->url ? $value->url : "https://www.youtube.com/watch?v=DNkNjHW8HiE";

// Parse URL and get query parameters
                parse_str(parse_url($url, PHP_URL_QUERY), $query);

// Get the video ID
                $videoId = $query['v'] ?? null;

// Build the embed link
                $embedLink = $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;

                $row    = [];
                $row[]  = $no;
                $row[]  = $value->title;
                $iframe = '<iframe width="400" height="220" src="' . $embedLink . '"
               title="YouTube video player" frameborder="0"
               allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
               referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
                $row[]  = $iframe;
                $row[]  = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'url'   => 'required',
            ]);

            $collection = collect($request->all());
            $collection = $this->track_data($collection, $request->update_id);
            $result     = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
            $output     = $this->store_message($result, $request->update_id);

            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax() && permission('brand-edit')) {
            $data   = $this->model->findOrFail($request->id);
            $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('brand-delete')) {
            $result = $this->model->find($request->id)->delete();
            $output = $this->delete_message($result);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
