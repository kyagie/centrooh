<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class OneTimePassword extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number',
        'otp_code',
        'verified',
        'expires_at',
        'attempts'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
        'expires_at' => 'datetime',
        'attempts' => 'integer'
    ];

    /**
     * Generate a random OTP code.
     *
     * @param int $length
     * @return string
     */
    public static function generateOtp(int $length = 4): string
    {
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Create or update an OTP for a given phone number.
     *
     * @param string $phoneNumber
     * @param int $expiryMinutes
     * @return self
     */
    public static function createForPhone(string $phoneNumber, int $expiryMinutes = 10): self
    {
        // Normalize the phone number (remove any non-numeric characters)
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Check if there's an existing OTP record for this phone
        $otp = self::where('phone_number', $normalizedPhone)
                  ->first();
        
        // Generate a new OTP code
        $otpCode = self::generateOtp();
        
        if ($otp) {
            // Update existing OTP
            $otp->update([
                'otp_code' => $otpCode,
                'verified' => false,
                'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
                'attempts' => 0
            ]);
        } else {
            // Create new OTP
            $otp = self::create([
                'phone_number' => $normalizedPhone,
                'otp_code' => $otpCode,
                'verified' => false,
                'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
                'attempts' => 0
            ]);
        }
        
        return $otp;
    }

    /**
     * Verify an OTP code for a given phone number.
     *
     * @param string $phoneNumber
     * @param string $otpCode
     * @return bool
     */
    public static function verifyOtp(string $phoneNumber, string $otpCode): bool
    {
        // Normalize the phone number
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Get the latest OTP for this phone number
        $otp = self::where('phone_number', $normalizedPhone)
                  ->where('verified', false)
                  ->where('expires_at', '>', Carbon::now())
                  ->first();
        
        if (!$otp) {
            return false;
        }
        
        // Increment attempts
        $otp->increment('attempts');
        
        // Check if the OTP code matches
        if ($otp->otp_code === $otpCode) {
            $otp->update(['verified' => true]);
            return true;
        }
        
        return false;
    }

    /**
     * Check if an OTP has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if an OTP has been verified.
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }
    
    /**
     * Check if an OTP has exceeded maximum attempts.
     *
     * @param int $maxAttempts
     * @return bool
     */
    public function hasExceededMaxAttempts(int $maxAttempts = 3): bool
    {
        return $this->attempts >= $maxAttempts;
    }
}
