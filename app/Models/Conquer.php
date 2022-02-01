<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conquer extends Model
{
    use HasFactory;
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
     * Village's previous player
     * @return Player A player record
     */
    public function previous_player() { return $this->belongsTo(Player::class, 'prevpid'); }
    
    /**
     * Village's next player
     * @return Player A player record
     */
    public function next_player() { return $this->belongsTo(Player::class, 'nextpid'); }

    /**
     * Village's previous tribe
     * @return Tribe A tribe record
     */
    public function previous_tribe() { return $this->belongsTo(Tribe::class, 'prevtid'); }
    
    /**
     * Village's next tribe
     * @return Tribe A tribe record
     */
    public function next_tribe() { return $this->belongsTo(Tribe::class, 'nexttid'); }

}