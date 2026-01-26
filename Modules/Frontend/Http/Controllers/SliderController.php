<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontend\Entities\Slider;

class SliderController extends BaseController
{
    public function slider()
    {
        $setTitle = __('Slider');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Slider::all();
        return view('frontend::slider', compact('data'));
    }

    public function create(Request $request)
    {
        $data = new Slider();
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('storage', $filename);
            $data->image = $filename;
        }
        $data->url = $request->url ?? '';
        $data->save();
        return redirect()->back();
    }

    public function delete($id)
    {
        Slider::find($id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $setTitle = __(' Edit Slider');
        $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
        $data = Slider::find($id);
        return view('frontend::edit.editslider', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = Slider::find($id);
        if ($request->File('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('storage', $filename);
            $data->image = $filename;
        } else {
            $data->image = $data->image;
        }
        $data->url = $request->url;
        $data->update();
        return redirect()->route('admin.slider');
    }
}
