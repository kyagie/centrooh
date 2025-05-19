<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgentNotificationType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'status',
        'created_by',
    ];

    //Automatically create slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
        static::updating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function agentNotifications()
    {
        return $this->hasMany(AgentNotification::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    
}
