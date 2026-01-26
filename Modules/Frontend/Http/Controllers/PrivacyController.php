<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontend\Entities\Privacy;

class PrivacyController extends BaseController
{
    public function privacy()
    {
        $setTitle = __('Privacy Policy');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Privacy::all();
        return view('frontend::privacy', compact('data'));
    }

    public function create(Request $request)
    {
        $collection = collect(['details' => $request->details]);
        Privacy::create($collection->all());
        return redirect()->back();
    }

    public function delete($id)
    {
        Privacy::find($id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $setTitle = __(' Edit Privacy Policy');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Privacy::find($id);
        return view('frontend::edit.editprivacy', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = Privacy::find($id);
        $data->details = $request->details;
        $data->update();
        return redirect()->route('admin.privacy');
    }
}
