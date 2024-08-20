<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = ['report_id', 'status_from', 'status_to', 'clarification'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}