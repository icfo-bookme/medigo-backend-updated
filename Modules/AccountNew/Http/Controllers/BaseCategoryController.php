<?php


namespace Modules\AccountNew\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BaseCategory;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BaseCategoryController extends Controller
{
    /*
    |----------------------------------------
    | View Page
    |----------------------------------------
    */
    public function index()
    {
        return view('base_category.index');
    }

    /*
    |----------------------------------------
    | Yajra DataTable List
    |----------------------------------------
    */
    public function list(Request $request)
    {



        $data = BaseCategory::with(['createdBy', 'updatedBy'])->select('base_category.*');

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('created_by', function ($row) {
                return $row->createdBy ? $row->createdBy->name : '';
            })

            ->addColumn('updated_by', function ($row) {
                return $row->updatedBy ? $row->updatedBy->name : '';
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d') : '';
            })

            ->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at->format('Y-m-d') : '';
            })

            ->addColumn('action', function ($row) {
                return '
    <button onclick="editData(' . $row->id . ')" class="bg-blue-500 text-white px-2 py-1 rounded">Edit</button>
    <button onclick="deleteData(' . $row->id . ')" class="bg-red-500 text-white px-2 py-1 rounded ml-2">Delete</button>
    <a href="' . route('costInsertView', ['id' => $row->id]) . '" class="bg-green-500 text-white px-2 py-1 rounded ml-2">Add Cost</a>
    <a href="' . route('fundInsertView', ['id' => $row->id]) . '" class="bg-green-500 text-white px-2 py-1 rounded ml-2">Add Fund</a>
';
            })

            ->rawColumns(['action'])

            ->make(true);
    }

    /*
    |----------------------------------------
    | Store
    |----------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        BaseCategory::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully'
        ]);
    }

    /*
    |----------------------------------------
    | Edit
    |----------------------------------------
    */
    public function edit($id)
    {
        $data = BaseCategory::findOrFail($id);
        return response()->json($data);
    }

    /*
    |----------------------------------------
    | Update
    |----------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $data = BaseCategory::findOrFail($id);

        $data->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    }

    /*
    |----------------------------------------
    | Delete
    |----------------------------------------
    */
    public function destroy($id)
    {
        $data = BaseCategory::findOrFail($id);
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
