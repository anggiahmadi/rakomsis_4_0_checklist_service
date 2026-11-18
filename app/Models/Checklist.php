<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'tenant_id',
        'form_template_id',
        'code',
        'name',
    ];
}
