<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillboardReviewNote extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'billboard_id',
        'note',
        'created_by'
    ];
    
    /**
     * Get the billboard that this review note belongs to.
     */
    public function billboard()
    {
        return $this->belongsTo(Billboard::class);
    }
    
    /**
     * Get the user who created the review note.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
