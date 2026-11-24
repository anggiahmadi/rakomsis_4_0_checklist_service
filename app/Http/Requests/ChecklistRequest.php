<?php

namespace App\Http\Requests;

use App\Models\Checklist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChecklistRequest extends FormRequest
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
            'quantity' => 'required|integer',
            'period' => 'required|in:hour,day,week,two weeks,month,two months,quarter,half year,year',
            'mon' => 'required|boolean',
            'tue' => 'required|boolean',
            'wed' => 'required|boolean',
            'thu' => 'required|boolean',
            'fri' => 'required|boolean',
            'sat' => 'required|boolean',
            'sun' => 'required|boolean',
            'need_approval' => 'required|boolean',
            'has_save_to_draft' => 'required|boolean',
            'has_unfinish_notification' => 'required|boolean',
            'notification_started_at' => 'nullable|date',
            'divisions' => 'nullable|array',
            'divisions.*.division_id' => 'required|integer',
        ];
    }

    public function store()
    {
        $this->validate([
            'code' => Rule::unique('checklists')->where('tenant_id', $this->tenant_id),
        ]);

        DB::beginTransaction();

        $checklist = Checklist::create($this->validated());

        if ($this->has('divisions')) {
            $divisions = (!is_array($this->divisions)) ? json_decode($this->divisions) : json_decode(json_encode($this->divisions));

            foreach ($divisions as $division) {
                DB::table('checklist_divisions')->insert([
                    'checklist_id' => $checklist->id,
                    'division_id' => $division->division_id,
                ]);
            }
        }

        DB::commit();

        return $checklist;
    }

    public function update(int $id)
    {
        DB::beginTransaction();

        $this->validate([
            'code' => Rule::unique('checklists')->where('tenant_id', $this->tenant_id)->ignore($id),
        ]);

        $checklist = Checklist::findOrFail($id);

        $checklist->update($this->validated());

        if ($this->has('divisions')) {
            DB::table('checklist_divisions')->where('checklist_id', $checklist->id)->delete();

            $divisions = (!is_array($this->divisions)) ? json_decode($this->divisions) : json_decode(json_encode($this->divisions));

            foreach ($divisions as $division) {
                DB::table('checklist_divisions')->insert([
                    'checklist_id' => $checklist->id,
                    'division_id' => $division->division_id,
                ]);
            }
        }

        DB::commit();

        return $checklist;
    }
}
