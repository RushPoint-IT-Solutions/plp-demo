<?php

namespace App\Http\Controllers;

use App\Religion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReligionController extends Controller
{
    public function index()
    {
        $totalCount = Religion::count();
        return view('registrar.process.religion.index', compact('totalCount'));
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'];
        $order = $request->get('order')[0];
        $columnIndex = $order['column'];
        $columnName = $request->get('columns')[$columnIndex]['data'];
        $columnSortOrder = $order['dir'];

        $query = Religion::with('createdBy');

        $totalRecords = Religion::count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        $allowedColumns = ['id', 'name', 'created_at'];
        if (in_array($columnName, $allowedColumns)) {
            $query->orderBy($columnName, $columnSortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $religions = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($religions as $religion) {
            $actions  = '<div class="dropdown">';
            $actions .= '<button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">';
            $actions .= '<i class="ri-more-2-fill"></i></button>';
            $actions .= '<ul class="dropdown-menu">';

            $actions .= '<li><button type="button" class="dropdown-item edit-religion" data-id="' . $religion->id . '">
                            <i class="ri-edit-line me-2"></i>Edit</button></li>';
                            
            $actions .= '<li><button class="dropdown-item text-danger delete-religion" data-id="' . $religion->id . '">
                            <i class="ri-delete-bin-line me-2"></i>Delete</button></li>';

            $actions .= '</ul></div>';

            $data[] = [
                'action' => $actions,
                'name' => $religion->name,
                'created_by' => $religion->createdBy ? $religion->createdBy->name : '<span class="text-muted">—</span>',
                'created_at' => $religion->created_at->format('M d, Y'),
            ];
        }

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:religions,name',
        ]);

        $religion = new Religion();
        $religion->name = $request->name;
        $religion->created_by  = Auth::id();
        $religion->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Religion added successfully!',
            ]);
        }

        return back();
    }

    public function edit($id)
    {
        $religion = Religion::findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'religion' => $religion,
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:religions,name,' . $id,
        ]);

        $religion = Religion::findOrFail($id);
        $religion->name = $request->name;
        $religion->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Religion updated successfully!',
            ]);
        }

        return back();
    }

    public function destroy($id)
    {
        $religion = Religion::findOrFail($id);
        $religion->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Religion deleted successfully!',
            ]);
        }

        return back();
    }
}