<?php

namespace App\Http\Requests;

use App\Models\ChecklistResult;
use App\Traits\ConsumesExternalService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChecklistResultRequest extends FormRequest
{
    use ConsumesExternalService;

    public string $baseUri, $secret;
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
            'checklist_id' => 'required|integer|exists:checklists,id',
            'custom_form_id' => 'required|integer',
            'employee_id' => 'nullable|integer',
            'asset_id' => 'nullable|integer',
            'room_id' => 'nullable|integer',
            'floor_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'checklist_name' => 'nullable|string|max:255',
            'checklist_desc' => 'nullable|string|max:255',
            'employee_name' => 'nullable|string|max:255',
            'allotment_code' => 'nullable|string|max:255',
            'allotment_name' => 'nullable|string|max:255',
            'asset_name' => 'nullable|string|max:255',
            'room_name' => 'nullable|string|max:255',
            'floor_name' => 'nullable|string|max:255',
            'location_code' => 'nullable|string|max:255',
            'location_name' => 'nullable|string|max:255',
            'allotment' => 'required|in:location,floor,room,asset',
            'is_checked' => 'required|boolean',
            'period' => 'required|in:hour,day,week,two weeks,month,quarter,half year,year',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'filled_at' => 'nullable|date',
            'has_geolocation' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'has_abnormal_answer' => 'required|boolean',
            'is_overdue' => 'required|boolean',
            'need_approval' => 'required|boolean',
            'has_save_to_draft' => 'required|boolean',
            'total_reject' => 'required|integer',
            'reject_reason' => 'nullable|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:255',
            'is_finished' => 'required|boolean',
            'status' => 'required|in:draft,posted',
            'back_to_draft_by' => 'nullable|string|max:255',
            'deleted_by' => 'nullable|string|max:255',
        ];
    }

    public function store()
    {
        $checklistResult = ChecklistResult::create($this->validated());

        // Start : Update Custom Form ID

        $this->baseUri = config('services.files.base_uri');

        $this->secret = config('services.files.secret');

        $data = [
            'tenant_id' => $checklistResult->tenant_id,
            'source' => 'checklist_result',
            'source_id' => $checklistResult->id
        ];

        $returnRequest = (object) self::performRequest('PUT', '/api/custom_forms/' . $this->custom_form_id, $data);

        // End : Update Custom Form ID

        return $checklistResult;
    }

    public function update(ChecklistResult $checklistResult)
    {
        $checklistResult->update($this->validated());

        return $checklistResult;
    }
}
