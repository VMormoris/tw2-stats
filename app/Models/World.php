<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class World extends Model
{
    use HasFactory;
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable =
    [
        'wid', 'name', 'server', 'url', 'win_condition', 'win_ammount',
        'finished', 'running',
        'moral', 'relocation', 'night_bonus',
        'time_offset',
        'start', 'end',
        'night_start', 'night_end'
    ];

}