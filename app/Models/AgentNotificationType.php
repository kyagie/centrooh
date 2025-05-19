<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentNotificationType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'status',
        'created_by',
    ];

    public function agentNotifications()
    {
        return $this->hasMany(AgentNotification::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    
}
