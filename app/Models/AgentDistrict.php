<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class AgentDistrict extends Model implements AuditableContract
{
    use HasFactory, Auditable;

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
