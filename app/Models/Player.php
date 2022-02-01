<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Gets player's history (One to Many Relationship between Player and PlayerHistory)
     * @return HasMany Relationship betweem Player and Players' History
     */
    public function history() { return $this->hasMany(PlayerHistory::class, 'pid'); }

    /**
     * Gets Player's tribe (Inverse One to Many Relationship between Tribe and Player)
     * @return Tribe A tribe record 
     */
    public function tribe() { return $this->belongsTo(Tribe::class, 'tid'); }

}
