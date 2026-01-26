<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontend\Entities\Refund;

class RefundController extends BaseController
{
    public function refund()
    {
        $setTitle = __('Refund & Return Policy');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Refund::all();
        return view('frontend::refund', compact('data'));
    }

    public function create(Request $request)
    {
        $collection = collect(['details' => $request->details]);
        Refund::create($collection->all());
        return redirect()->back();
    }

    public function delete($id)
    {
        Refund::find($id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $setTitle = __(' Edit Return & Refund Policy');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Refund::find($id);
        return view('frontend::edit.editrefund', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = Refund::find($id);
        $data->details = $request->details;
        $data->update();
        return redirect()->route('admin.refund');
    }
}
