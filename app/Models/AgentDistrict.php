<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDistrict extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'district_id',
    ];

    /**
     * The agent that belongs to this pivot.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * The district that belongs to this pivot.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
