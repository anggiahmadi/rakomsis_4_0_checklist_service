<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTargetDetail extends Model
{
    protected $fillable = [
        'tenant_id',
        'checklist_target_id',
        'checklist_id',
        'location_id',
        'floor_id',
        'room_id',
        'asset_id',
        'allotment',
        'allotment_code',
        'allotment_name',
        'timezone',
    ];

    public function checklistTarget()
    {
        return $this->belongsTo(ChecklistTarget::class, 'checklist_target_id');
    }

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }
}
