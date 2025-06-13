<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Agent extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Notifiable, Auditable;

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
    ];

    protected $appends = ['profile_picture_url', 'member_since'];


    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture ? Storage::url($this->profile_picture) : null;
    }
    
    /**
     * Get the member since date in a human-readable format.
     *
     * @return string
     */
    public function getMemberSinceAttribute()
    {
        return $this->created_at ? $this->created_at->format('F j, Y') : null;
    }

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
     * Get the devices associated with the agent.
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    /**
     * The billboards assigned to this agent (many-to-many).
     */
    public function billboards()
    {
        return $this->belongsToMany(Billboard::class, 'agent_billboard');
    }

    /**
     * Get billboard images uploaded by this agent.
     */
    public function billboardImages()
    {
        return $this->hasMany(BillboardImage::class)->where('uploader_type', 'agent');
    }

    /**
     * Get the agent's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(AgentNotification::class);
    }

    /**
     * The districts this agent is assigned to.
     */
    public function districts()
    {
        return $this->belongsToMany(District::class, 'agent_districts');
    }

    /**
     * The agent-district pivot records for this agent.
     */
    public function agentDistricts()
    {
        return $this->hasMany(AgentDistrict::class);
    }

    /**
     * Generate a unique username based on the agent's first and last name.
     *
     * @param string $firstName The agent's first name
     * @param string $lastName The agent's last name
     * @return string A unique username
     */
    public static function generateUniqueUsername(string $firstName, string $lastName): string
    {
        // Convert to lowercase and combine first and last name with a dot
        $baseUsername = strtolower($firstName . '.' . $lastName);

        // Remove any non-alphanumeric characters except dots
        $baseUsername = preg_replace('/[^a-z0-9.]/', '', $baseUsername);

        // Check if username exists, if so, add a counter
        $username = $baseUsername;
        $counter = 1;

        while (self::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
