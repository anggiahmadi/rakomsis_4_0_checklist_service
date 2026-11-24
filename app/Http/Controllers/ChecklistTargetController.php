<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Http\Requests\ChecklistTargetRequest;
use App\Models\ChecklistTarget;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

    public function getCountTargetResults(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'code' => 'required|string',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|gte:started_at',
            'location_ids' => 'required|array',
            'checklist_ids' => 'nullable|array',
        ]);

        $targets = DB::table('checklist_target_details as ctd')
            ->join('checklist_targets as ct', 'ctd.checklist_target_id', 'ct.id')
            ->where('ctd.tenant_id', $request->tenant_id)
            ->whereIn('ctd.location_id', $request->location_ids)
            ->where('ct.code', $request->code)
            ->where('ct.started_at', '>=', $request->started_at)
            ->where('ct.ended_at', '<=', $request->ended_at);

        $results = DB::table('checklist_results as cr')
            ->where('cr.tenant_id', $request->tenant_id)
            ->whereIn('cr.location_id', $request->location_ids)
            ->where('cr.started_at', '>=', $request->started_at)
            ->where('cr.ended_at', '<=', $request->ended_at);

        if ($request->has('checklist_ids')) {
            $targets = $targets->whereIn('ctd.checklist_id', $request->checklist_ids);

            $results = $results->whereIn('cr.checklist_id', $request->checklist_ids);
        }

        if ($request->has('allotment_code')) {
            $targets = $targets->where('ct.allotment_code', $request->allotment_code);

            $results = $results->where('cr.allotment_code', $request->allotment_code);
        }

        $data['targets'] = $targets->count();

        $data['results'] = $results->count();

        return $this->successResponse($data);
    }

    public function resumePerPeriod(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'code' => 'required|string',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|gte:started_at',
            'total_holidays' => 'required|integer',
            'location_ids' => 'required|array',
            'checklist_ids' => 'nullable|array',
            'allotment_code' => 'nullable|string',
        ]);

        $startedAt = date('Y-m-d 00:00:00', strtotime($request->started_at));
        $endedAt = date('Y-m-d 23:59:59', strtotime($request->ended_at));

        // --- Period calculations, done ONCE in PHP (not per row) to keep the query fast ---
        $periodStart = Carbon::parse($startedAt)->startOfDay();
        $periodEnd   = Carbon::parse($endedAt)->endOfDay();

        $totalDays     = $periodStart->diffInDays($periodEnd) + 1; // inclusive day count
        $totalMonths   = ($totalDays < $periodStart->daysInMonth) ? 1 : round($totalDays / $periodStart->daysInMonth); // proportional to the month's length
        $totalQuarters = ($totalDays < 90) ? 1 : round($totalDays / 90); // approximate quarter length
        $totalHalfYears = ($totalDays < 182) ? 1 : round($totalDays / 182); // approximate half-year length
        $totalYears    = ($totalDays < 365) ? 1 : round($totalDays / 365); // approximate year

        $totalMondays = 0;
        $totalTuesdays = 0;
        $totalWednesdays = 0;
        $totalThursdays = 0;
        $totalFridays = 0;
        $totalSaturdays = 0;
        $totalSundays = 0;

        $totalHolidays = 0;

        foreach (CarbonPeriod::create($periodStart, $periodEnd) as $date) {
            if ($date->isWeekend()) {
                $totalHolidays++;
            }

            if ($date->isMonday())    { $totalMondays++; }
            if ($date->isTuesday())   { $totalTuesdays++; }
            if ($date->isWednesday()) { $totalWednesdays++; }
            if ($date->isThursday())  { $totalThursdays++; }
            if ($date->isFriday())    { $totalFridays++; }
            if ($date->isSaturday())  { $totalSaturdays++;}
            if ($date->isSunday())    { $totalSundays++;}
        }

        $totalWeeks = max([
            $totalMondays,
            $totalTuesdays,
            $totalWednesdays,
            $totalThursdays,
            $totalFridays,
            $totalSaturdays,
            $totalSundays
        ]);

        $totalTwoWeeks = ($totalWeeks == 0) ? 0 : ceil($totalWeeks / 2);

        $totalHolidays += $request->total_holidays; // Initialize total holidays from request

        $totalWeekdays = $totalDays - $totalSaturdays - $totalSundays - $totalHolidays;

        // Subquery: target_total_checklist pre-aggregated per period
        $targetPerPeriod = DB::table('checklists as c')
            ->join('checklist_target_details as ctd', 'ctd.checklist_id', 'c.id')
            ->join('checklist_targets as ct', 'ct.id', 'ctd.checklist_target_id')
            ->where('c.tenant_id', $request->tenant_id)
            ->where('ct.code', $request->code)
            ->where('ct.ended_at', '>=', $request->started_at)
            ->where('ct.started_at', '<=', $request->ended_at)
            ->whereIn('ctd.location_id', $request->location_ids)
            ->groupBy('c.period')
            ->select([
                'c.period',
                DB::raw("
                    COALESCE(SUM(
                        c.quantity *
                        CASE c.period
                            WHEN 'hour'      THEN $totalWeekdays
                            WHEN 'day'       THEN $totalWeekdays
                            WHEN 'week'      THEN $totalWeeks
                            WHEN 'two weeks' THEN $totalTwoWeeks
                            WHEN 'month'     THEN $totalMonths
                            WHEN 'quarter'   THEN $totalQuarters
                            WHEN 'half_year' THEN $totalHalfYears
                            WHEN 'year'      THEN $totalYears
                            ELSE 1
                        END
                    ), 0) as target_total_checklist
                "),
            ]);

        if ($request->has('allotment_code')) {
            $targetPerPeriod = $targetPerPeriod->where('ctd.allotment_code', $request->allotment_code);
        }

        if ($request->has('checklist_ids')) {
            $targetPerPeriod = $targetPerPeriod->whereIn('ctd.checklist_id', $request->checklist_ids);
        }

        // Main query: join pre-aggregated subquery on period
        $data = DB::table('checklist_results as cr')
            ->leftJoinSub($targetPerPeriod, 'tpp', function ($join) {
                $join->on('tpp.period', '=', 'cr.period');
            })
            ->where('cr.tenant_id', $request->tenant_id)
            ->where('cr.ended_at', '>=', $startedAt)
            ->where('cr.started_at', '<=', $endedAt)
            ->whereNull('cr.deleted_at')
            ->whereIn('cr.location_id', $request->location_ids)
            ->groupBy('cr.period', 'tpp.target_total_checklist')
            ->select([
                'cr.period',
                DB::raw('SUM(CASE WHEN cr.is_finished = 1 THEN 1 ELSE 0 END) as total_finish'),
                DB::raw('SUM(CASE WHEN cr.is_checked = 0 THEN 1 ELSE 0 END) as total_not_finish'),
                DB::raw('SUM(CASE WHEN cr.is_checked = 1 AND cr.need_approval = 1 AND cr.approved_by IS NULL THEN 1 ELSE 0 END) as total_need_approval'),
                DB::raw('COUNT(cr.id) as total_checklist'),
                DB::raw('COALESCE(tpp.target_total_checklist, 0) as total_target_checklist'),
            ]);

        if ($request->has('allotment_code')) {
            $data = $data->where('cr.allotment_code', $request->allotment_code);
        }

        if ($request->has('checklist_ids')) {
            $data = $data->whereIn('cr.checklist_id', $request->checklist_ids);
        }

        $data = $data->get();

        return $this->successResponse($data);
    }
}
