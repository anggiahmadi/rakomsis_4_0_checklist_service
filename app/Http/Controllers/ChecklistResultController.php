<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Requests\ChecklistResultRequest;
use App\Models\ChecklistResult;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ChecklistResultController extends Controller
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
    public function store(ChecklistResultRequest $request)
    {
        return $this->successResponse($request->store(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $checklistResult = ChecklistResult::with('checklist')->findOrFail($id);

        return $this->successResponse($checklistResult);
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

            $data = ChecklistResult::withTrashed()->findOrFail($id);

            $data->forceDelete();

            DB::commit();
        } else {
            $data = ChecklistResult::findOrFail($id);

            $data->delete();
        }

        return $this->successResponse('deleted data is successfull');
    }

    public function getData(object $request)
    {
        $query = ChecklistResult::query()->where('tenant_id', $request->tenant_id);

        if ($request->data_type == 'softdelete') $query->onlyTrashed();

        $keyword = $request->input('search.value');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('checklist_name', 'like', "%{$keyword}%")
                    ->orWhere('checklist_desc', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('employee_name', 'like', "%{$keyword}%")
                    ->orWhere('allotment_code', 'like', "%{$keyword}%")
                    ->orWhere('allotment_name', 'like', "%{$keyword}%")
                    ->orWhere('asset_name', 'like', "%{$keyword}%")
                    ->orWhere('room_name', 'like', "%{$keyword}%")
                    ->orWhere('floor_name', 'like', "%{$keyword}%")
                    ->orWhere('location_code', 'like', "%{$keyword}%")
                    ->orWhere('location_name', 'like', "%{$keyword}%")
                    ->orWhere('allotment', 'like', "%{$keyword}%")
                    ->orWhere('is_checked', 'like', "%{$keyword}%")
                    ->orWhere('period', 'like', "%{$keyword}%")
                    ->orWhere('started_at', 'like', "%{$keyword}%")
                    ->orWhere('ended_at', 'like', "%{$keyword}%")
                    ->orWhere('filled_at', 'like', "%{$keyword}%");
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
            ->filterColumn('checklist_name', fn($q, $k) => $q->where('checklist_name', 'like', "%{$k}%"))
            ->filterColumn('checklist_desc', fn($q, $k) => $q->where('checklist_desc', 'like', "%{$k}%"))
            ->filterColumn('customer_name', fn($q, $k) => $q->where('customer_name', 'like', "%{$k}%"))
            ->filterColumn('employee_name', fn($q, $k) => $q->where('employee_name', 'like', "%{$k}%"))
            ->filterColumn('allotment_code', fn($q, $k) => $q->where('allotment_code', 'like', "%{$k}%"))
            ->filterColumn('allotment_name', fn($q, $k) => $q->where('allotment_name', 'like', "%{$k}%"))
            ->filterColumn('asset_name', fn($q, $k) => $q->where('asset_name', 'like', "%{$k}%"))
            ->filterColumn('room_name', fn($q, $k) => $q->where('room_name', 'like', "%{$k}%"))
            ->filterColumn('floor_name', fn($q, $k) => $q->where('floor_name', 'like', "%{$k}%"))
            ->filterColumn('location_code', fn($q, $k) => $q->where('location_code', 'like', "%{$k}%"))
            ->filterColumn('location_name', fn($q, $k) => $q->where('location_name', 'like', "%{$k}%"))
            ->filterColumn('allotment', fn($q, $k) => $q->where('allotment', 'like', "%{$k}%"))
            ->filterColumn('is_checked', fn($q, $k) => $q->where('is_checked', 'like', "%{$k}%"))
            ->filterColumn('period', fn($q, $k) => $q->where('period', 'like', "%{$k}%"))
            ->filterColumn('started_at', fn($q, $k) => $q->where('started_at', 'like', "%{$k}%"))
            ->filterColumn('ended_at', fn($q, $k) => $q->where('ended_at', 'like', "%{$k}%"))
            ->filterColumn('filled_at', fn($q, $k) => $q->where('filled_at', 'like', "%{$k}%"))
            ->toJson();
    }
}
