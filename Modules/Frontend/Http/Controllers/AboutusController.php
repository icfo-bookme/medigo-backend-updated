<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontend\Entities\About;

class AboutusController extends BaseController
{
    public function aboutus()
    {
        $setTitle = __('About Us');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = About::all();
        return view('frontend::aboutus', compact('data'));
    }

    public function create(Request $request)
    {
        $collection = collect(['details' => $request->details]);
        About::create($collection->all());
        return redirect()->back();
    }

    public function delete($id)
    {
        About::find($id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $setTitle = __(' Edit About Us');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = About::find($id);
        return view('frontend::edit.editaboutus', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = About::find($id);
        $data->details = $request->details;
        $data->update();
        return redirect()->route('admin.aboutus');
    }

}
