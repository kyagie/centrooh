<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Guarded attributes.
     * 
     * @var array<int, string>
     * 
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'profile_picture',
        'phone_number',
        'status',
        'user_id',
        'created_by',
        'approved_by',
        'region_id',
        'district_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_by',
        'approved_by',
        'deleted_at'
    ];

    /**
     * Get the user associated with the agent.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the user who created the agent.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who approved the agent.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get the region associated with the agent.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    
    /**
     * Get the district associated with the agent.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
    /**
     * Get the billboards assigned to this agent.
     */
    public function billboards()
    {
        return $this->hasMany(Billboard::class);
    }
}
