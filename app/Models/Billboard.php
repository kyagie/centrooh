<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billboard extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'is_active',
        'address',
        'location',
        'latitude',
        'longitude',
        'update_interval',
        'district_id',
        'agent_id',
        'created_by',
        'reviewed_by',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
    
    /**
     * Get the district that owns the billboard.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
    /**
     * Get the agent assigned to the billboard.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    
    /**
     * Get the user who created the billboard.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who reviewed the billboard.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    /**
     * Get the site code for this billboard.
     */
    public function siteCode()
    {
        return $this->hasOne(BillboardSiteCode::class);
    }
    
    /**
     * Get the review notes for this billboard.
     */
    public function reviewNotes()
    {
        return $this->hasMany(BillboardReviewNote::class);
    }
    
    /**
     * Scope a query to only include active billboards.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope a query to filter billboards by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
