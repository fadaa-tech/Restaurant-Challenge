<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'date',
        'status',
        'verification_response',
        'report_response',
        'confirmation_response',
        'total_revenue',
    ];

    protected $casts = [
        'verification_response' => 'array',
        'report_response' => 'array',
        'confirmation_response' => 'array',
        'date' => 'date',
    ];
}
