<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTarget extends Model
{
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'started_at',
        'ended_at'
    ];

    public function checklistTargetDetails()
    {
        return $this->hasMany(ChecklistTargetDetail::class, 'checklist_target_id');
    }
}
