<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tribe extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = array('id', 'name', 'nname', 'tag', 'active', 'members', 'points', 'ppm', 'ppv', 'offbash', 'defbash', 'totalbash', 'rankno', 'villages', 'vp', 'timestamp');

    /**
     * Gets tribe's members (One to Many Relationship between Tribe and Players)
     * @return HasMany Relationship betweem Tribe and Players
     */
    public function members() { return $this->hasMany(Player::class, 'tid'); }

    /**
     * Gets tribe's history (One to Many Relationship between Tribe and TribeHistory)
     * @return HasMany Relationship betweem Tribe and Tribes' History
     */
    public function history() { return $this->hasMany(TribeHistory::class, 'tid'); }

}
