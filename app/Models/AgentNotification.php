<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class AgentNotification extends Model implements AuditableContract
{
    use Auditable;

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

    protected $appends = ['sent_at'];

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

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
    
    public function getSentAtAttribute()
    {
        return $this->created_at->format('M d, Y \a\t h:i A');
    }
}
