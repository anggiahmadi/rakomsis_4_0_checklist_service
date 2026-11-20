<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Http\Requests\ChecklistTargetRequest;
use App\Models\ChecklistTarget;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ChecklistTargetController extends Controller
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
    public function store(ChecklistTargetRequest $request)
    {
        return $this->successResponse($request->store(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $checklistTarget = ChecklistTarget::with('checklistTargetDetails')->findOrFail($id);

        return $this->successResponse($checklistTarget);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

            $data = ChecklistTarget::withTrashed()->findOrFail($id);

            $data->forceDelete();

            DB::commit();
        } else {
            $data = ChecklistTarget::findOrFail($id);

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

        $data = ChecklistTarget::with('checklistTargetDetails')
            ->where('tenant_id', $request->tenant_id)
            ->where('code', $request->code)
            ->first();

        return $this->successResponse($data);
    }

    public function getCode(Request $request)
    {
        $request->validate(['tenant_id' => 'required|integer']);

        return $this->successResponse(
            Controller::getMasterCode('checklist_targets', 'CT', $request->tenant_id, $request)
        );
    }

    public function getData(object $request)
    {
        $query = ChecklistTarget::query()->where('tenant_id', $request->tenant_id);

        if ($request->data_type == 'softdelete') $query->onlyTrashed();

        $keyword = $request->input('search.value');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('started_at', 'like', "%{$keyword}%")
                    ->orWhere('ended_at', 'like', "%{$keyword}%");
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
            ->filterColumn('started_at', fn($q, $k) => $q->where('started_at', 'like', "%{$k}%"))
            ->filterColumn('ended_at', fn($q, $k) => $q->where('ended_at', 'like', "%{$k}%"))
            ->toJson();
    }
}
