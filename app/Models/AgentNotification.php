<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentNotification extends Model
{
    //
    protected $fillable = [
        'agent_id',
        'created_by',
        'agent_notification_type_id',
        'title',
        'body',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agentNotificationType()
    {
        return $this->belongsTo(AgentNotificationType::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
