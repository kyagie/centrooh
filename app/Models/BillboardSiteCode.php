<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class BillboardSiteCode extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'billboard_id',
        'created_by'
    ];
    
    
    /**
     * Get the billboard that owns the site code.
     */
    public function billboard()
    {
        return $this->belongsTo(Billboard::class);
    }
    
    /**
     * Get the user who created the site code.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
