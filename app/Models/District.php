<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
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
        'region_id',
        'created_by'
    ];
    
    /**
     * Get the region that this district belongs to.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    
    /**
     * Get the user who created the district.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the billboards in this district.
     */
    public function billboards()
    {
        return $this->hasMany(Billboard::class);
    }
    
    /**
     * Get the agents assigned to this district.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
}
