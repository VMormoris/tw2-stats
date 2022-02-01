<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'players_history';
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
     * Player's tribe
     * @return Tribe A tribe record
     */
    public function tribe() { return $this->belongsTo(Tribe::class, 'tid'); }


}
