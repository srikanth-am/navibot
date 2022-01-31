<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConformanceReportSummary extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'level', 'version', 'pass', 'fail', 'dna', 'severity_low', 'severity_medium', 'severity_high', 'severity_na'
    ];
}
