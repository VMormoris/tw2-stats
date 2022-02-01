<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Gets village's history (One to Many Relationship between Village and VillageHistory)
     * @return HasMany Relationship betweem Village and Villages' History
     */
    public function history() { return $this->hasMany(VillageHistory::class, 'vid'); }

    /**
     * Gets player (Inverse One to Many Relationship between Player and Village)
     * @return Player A player record
     */
    public function player() { return $this->belongsTo(Tribe::class, 'pid'); }

    /**
     * Gets tribe (Inverse One to Many Relationship between Tribe and Village)
     * @return Tribe A tribe record 
     */
    public function tribe() { return $this->belongsTo(Tribe::class, 'tid'); }

}
