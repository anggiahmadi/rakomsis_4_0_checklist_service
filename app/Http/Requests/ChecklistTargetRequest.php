<?php

namespace App\Http\Requests;

use App\Models\ChecklistTarget;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ChecklistTargetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => 'required|integer',
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|gte:started_at',
            'checklist_target_details' => 'required|array',
            'checklist_target_details.*.checklist_id' => 'required|integer|exists:checklists,id',
            'checklist_target_details.*.location_id' => 'nullable|integer',
            'checklist_target_details.*.floor_id' => 'nullable|integer',
            'checklist_target_details.*.room_id' => 'nullable|integer',
            'checklist_target_details.*.asset_id' => 'nullable|integer',
            'checklist_target_details.*.allotment' => 'required|in:location,floor,room,asset',
            'checklist_target_details.*.allotment_code' => 'required|string|max:255',
            'checklist_target_details.*.allotment_name' => 'required|string|max:255',
            'checklist_target_details.*.timezone' => 'required|string|max:255'
        ];
    }

    public function store()
    {
        try {
            DB::beginTransaction();

            $checklistTarget = ChecklistTarget::create($this->validated());

            $checklistTargetDetails = (!is_array($this->checklist_target_details)) ? json_decode($this->checklist_target_details) : json_decode(json_encode($this->checklist_target_details));

            foreach ($checklistTargetDetails as $detail) {
                $checklistTarget->checklistTargetDetails()->create((array) $detail);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return $checklistTarget;
    }

    public function update(int $id)
    {
        try {
            DB::beginTransaction();

            $checklistTarget = ChecklistTarget::findOrFail($id);

            $checklistTarget->update($this->validated());

            $checklistTarget->checklistTargetDetails()->delete();

            $checklistTargetDetails = (!is_array($this->checklist_target_details)) ? json_decode($this->checklist_target_details) : json_decode(json_encode($this->checklist_target_details));

            foreach ($checklistTargetDetails as $detail) {
                $checklistTarget->checklistTargetDetails()->create((array) $detail);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
        return $checklistTarget;
    }
}
