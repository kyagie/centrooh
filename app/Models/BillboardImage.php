<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillboardImage extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'billboard_id',
        'image_path',
        'image_type',
        'status',
        'is_primary',
        'uploader_type',
        'user_id',
        'agent_id',
        'meta',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'meta' => 'array',
    ];
    
    /**
     * Get the billboard that owns the image.
     */
    public function billboard()
    {
        return $this->belongsTo(Billboard::class);
    }
    
    /**
     * Get the user who uploaded the image (if uploaded by a user).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the agent who uploaded the image (if uploaded by an agent).
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    
    /**
     * Determine if the image was uploaded by a user.
     */
    public function isUploadedByUser()
    {
        return $this->uploader_type === 'user';
    }
    
    /**
     * Determine if the image was uploaded by an agent.
     */
    public function isUploadedByAgent()
    {
        return $this->uploader_type === 'agent';
    }
    
    /**
     * Get the uploader of the image (either user or agent).
     */
    public function uploader()
    {
        return $this->isUploadedByUser()
            ? $this->user
            : $this->agent;
    }
    
    /**
     * Scope a query to only include active images.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope a query to only include primary images.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
