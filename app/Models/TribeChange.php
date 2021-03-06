<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TribeChange extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tribe_changes';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Gets player (Inverse One to Many Relationship between Player and PlayerHistory)
     * @return Player A player record
     */
    public function player() { return $this->belongsTo(Player::class, 'pid'); }

    /**
     * Player's previous tribe
     * @return Tribe A tribe record
     */
    public function previous_tribe() { return $this->belongsTo(Tribe::class, 'prevtid'); }
    
    /**
     * Player's next tribe
     * @return Tribe A tribe record
     */
    public function next_tribe() { return $this->belongsTo(Tribe::class, 'nexttid'); }

}