<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontend\Entities\Term;

class TermsController extends BaseController
{
    public function terms()
    {
        $setTitle = __('Terms & Conditions');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Term::all();
        return view('frontend::Terms', compact('data'));
    }

    public function create(Request $request)
    {
        $collection = collect(['details' => $request->details]);
        Term::create($collection->all());
        return redirect()->back();
    }

    public function delete($id)
    {
        Term::find($id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $setTitle = __(' Edit About Us');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Term::find($id);
        return view('frontend::edit.editterms', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = Term::find($id);
        $data->details = $request->details;
        $data->update();
        return redirect()->route('admin.terms');
    }
}
