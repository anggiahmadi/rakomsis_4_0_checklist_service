<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'tenant_id',
        'form_template_id',
        'code',
        'name',
        'quantity',
        'period',
        'started_time_at',
        'ended_time_at',
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'sat',
        'sun',
        'need_approval',
        'has_save_to_draft',
        'has_unfinish_notification',
        'notification_started_at'
    ];

    public function checklistTargetDetail()
    {
        return $this->hasMany(ChecklistTargetDetail::class, 'checklist_id');
    }

    public function divisions()
    {
        return DB::table('checklist_divisions')->where('checklist_id', $this->id)->get();
    }
}
