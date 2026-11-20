<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistResult extends Model
{
    protected $fillable = [
        'tenant_id',
        'checklist_id',
        'custom_form_id',
        'employee_id',
        'asset_id',
        'room_id',
        'floor_id',
        'location_id',
        'checklist_name',
        'checklist_desc',
        'customer_name',
        'employee_name',
        'allotment_code',
        'allotment_name',
        'asset_name',
        'room_name',
        'floor_name',
        'location_code',
        'location_name',
        'sales_order_code',
        'allotment',
        'is_checked',
        'period',
        'started_at',
        'ended_at',
        'filled_at',
        'has_geolocation',
        'latitude',
        'longitude',
        'has_abnormal_answer',
        'is_overdue',
        'need_approval',
        'has_save_to_draft',
        'total_reject',
        'reject_reason',
        'approved_by',
        'timezone',
        'is_finished',
        'status',
        'back_to_draft_by',
        'deleted_by',
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }
}
