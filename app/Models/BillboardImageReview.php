<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class BillboardImageReview extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'billboard_image_id',
        'user_id',
        'review_note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the billboard image that this review belongs to.
     */
    public function billboardImage()
    {
        return $this->belongsTo(BillboardImage::class);
    }

    /**
     * Get the user who created the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
