<?php

namespace App\Models;

use App\Observers\BillboardObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

#[ObservedBy(BillboardObserver::class)]
class Billboard extends Model implements AuditableContract
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
        'is_active',
        'address',
        'area',
        'latitude',
        'longitude',
        'update_interval',
        'district_id',
        'created_by',
        'reviewed_by',
        'location'
    ];


    protected $appends = [
        'location',
        'last_updated',
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
     * Get all images for this billboard.
     */
    public function images()
    {
        return $this->hasMany(BillboardImage::class);
    }

    /**
     * Get the primary image for this billboard.
     */
    public function primaryImage()
    {
        return $this->hasOne(BillboardImage::class)->where('is_primary', true);
    }

    /**
     * Get the media owner that owns the billboard.
     */
    public function mediaOwner()
    {
        return $this->belongsTo(MediaOwner::class);
    }
    
    /**
     * The agents that are assigned to this billboard (many-to-many).
     */
    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'agent_billboard');
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

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->latitude,
            "lng" => (float)$this->longitude,
        ];
    }

    // public function setLocationAttribute(?array $location): void
    // {
    //     if (is_array($location)) {
    //         $this->attributes['latitude'] = $location['lat'];
    //         $this->attributes['longitude'] = $location['lng'];
    //         unset($this->attributes['location']);
    //     }
    // }

    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

    public static function getComputedLocation(): string
    {
        return 'location';
    }

    /**
     * Get the last updated date in a human-readable format.
     *
     * @return string|null
     */
    public function getLastUpdatedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('F j, Y') : null;
    }
}
