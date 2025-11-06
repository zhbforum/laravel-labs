<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['period_start', 'period_end', 'payload', 'path'];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'payload'      => 'array',
    ];
}
