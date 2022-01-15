<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TribeHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tribes_history';
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Gets tribe (Inverse One to Many Relationship between Tribe and TribeHistory)
     * @return Tribe A tribe record 
     */
    public function tribe() { return $this->belongsTo(Tribe::class, 'tid'); }
}
