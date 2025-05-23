<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Region extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'created_by'
    ];
    
    /**
     * Get the districts that belong to this region.
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }
    
    /**
     * Get the user who created the region.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
