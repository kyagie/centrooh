<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Agent;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the agent associated with the user.
     */
    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    /**
     * Get the agents created by the user.
     */
    public function createdAgents()
    {
        return $this->hasMany(Agent::class, 'created_by');
    }

    /**
     * Get the agents approved by the user.
     */
    public function approvedAgents()
    {
        return $this->hasMany(Agent::class, 'approved_by');
    }

    /**
     * Get the agents that the user is associated with.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class, 'user_id');
    }
    
    /**
     * Get the regions created by the user.
     */
    public function createdRegions()
    {
        return $this->hasMany(Region::class, 'created_by');
    }
    
    /**
     * Get the districts created by the user.
     */
    public function createdDistricts()
    {
        return $this->hasMany(District::class, 'created_by');
    }
    
    /**
     * Get the billboards created by the user.
     */
    public function createdBillboards()
    {
        return $this->hasMany(Billboard::class, 'created_by');
    }
    
    /**
     * Get the billboards reviewed by the user.
     */
    public function reviewedBillboards()
    {
        return $this->hasMany(Billboard::class, 'reviewed_by');
    }
    
    /**
     * Get billboard images uploaded by the user.
     */
    public function billboardImages()
    {
        return $this->hasMany(BillboardImage::class)->where('uploader_type', 'user');
    }

    public function canAccessPanel(Panel $panel): bool
    {
	    return true;
    }
}
