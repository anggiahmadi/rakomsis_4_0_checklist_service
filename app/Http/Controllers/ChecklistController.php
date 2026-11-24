<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistRequest;
use App\Models\Checklist;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ChecklistController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->getData($request);

        $request->has('page')
            ? $query = $query->paginate($this->getPerPage($request->total_per_page))
            : $query = $query->get();

        return $this->successResponse($query);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChecklistRequest $request)
    {
        return $this->successResponse($request->store(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $checklist = Checklist::with('divisions')->findOrFail($id);

        return $this->successResponse($checklist);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChecklistRequest $request, string $id)
    {
        return $this->successResponse($request->update($id), Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if ($request->is_permanent_delete) {
            DB::beginTransaction();

            $data = Checklist::withTrashed()->findOrFail($id);

            $data->forceDelete();

            DB::commit();
        } else {
            $data = Checklist::findOrFail($id);

            $data->delete();
        }

        return $this->successResponse('deleted data is successfull');
    }

    public function getByCode(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required',
            'code' => 'required'
        ]);

        $data = Checklist::with('divisions')
            ->where('tenant_id', $request->tenant_id)
            ->where('code', $request->code)
            ->first();

        return $this->successResponse($data);
    }

    public function getCode(Request $request)
    {
        $request->validate(['tenant_id' => 'required|integer']);

        return $this->successResponse(
            Controller::getMasterCode('checklists', 'C', $request->tenant_id, $request)
        );
    }

    public function getData(object $request)
    {
        $query = Checklist::query()->where('tenant_id', $request->tenant_id);

        if ($request->data_type == 'softdelete') $query->onlyTrashed();

        $keyword = $request->input('search.value');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('desc', 'like', "%{$keyword}%")
                    ->orWhere('quantity', 'like', "%{$keyword}%")
                    ->orWhere('period', 'like', "%{$keyword}%")
                    ->orWhere('need_approval', 'like', "%{$keyword}%")
                    ->orWhere('has_save_to_draft', 'like', "%{$keyword}%")
                    ->orWhere('has_unfinish_notification', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('order_by') && $request->filled('sort_by')) {
            $query->orderBy($request->order_by, $request->sort_by);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function datatables(Request $request)
    {
        $query = $this->getData($request);

        return DataTables::eloquent($query)
            ->filterColumn('code', fn($q, $k) => $q->where('code', 'like', "%{$k}%"))
            ->filterColumn('name', fn($q, $k) => $q->where('name', 'like', "%{$k}%"))
            ->filterColumn('quantity', fn($q, $k) => $q->where('quantity', 'like', "%{$k}%"))
            ->filterColumn('period', fn($q, $k) => $q->where('period', 'like', "%{$k}%"))
            ->filterColumn('need_approval', fn($q, $k) => $q->where('need_approval', 'like', "%{$k}%"))
            ->filterColumn('has_save_to_draft', fn($q, $k) => $q->where('has_save_to_draft', 'like', "%{$k}%"))
            ->filterColumn('has_unfinish_notification', fn($q, $k) => $q->where('has_unfinish_notification', 'like', "%{$k}%"))
            ->toJson();
    }
}
