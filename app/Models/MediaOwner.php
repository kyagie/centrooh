<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class MediaOwner extends Model implements AuditableContract
{
    use Auditable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'status',
    ];
  
    /**
     * Get the billboards owned by the media owner.
     */
    public function billboards()
    {
        return $this->hasMany(Billboard::class);
    }
}
