<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VillageHistory extends Model
{
    use HasFactory;
        
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'villages_history';
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Gets village (Inverse One to Many Relationship between Village and VillageHistory)
     * @return Village A village record
     */
    public function village() { return $this->belongsTo(Village::class, 'vid'); }
    
    /**
     * Village's player
     * @return Player A player record
     */
    public function player() { return $this->belongsTo(Player::class, 'pid'); }

    /**
     * Village's tribe
     * @return Tribe A tribe record
     */
    public function tribe() { return $this->belongsTo(Tribe::class, 'tid'); }
}
