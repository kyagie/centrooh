<?php

namespace App\Jobs;

use App\Models\OneTimePassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use AfricasTalking\SDK\AfricasTalking;

class SendOneTimePassword implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The OTP instance.
     *
     * @var \App\Models\OneTimePassword
     */
    protected $otp;

    /**
     * The prefix message to be sent with the OTP.
     *
     * @var string
     */
    protected $messagePrefix = '<#> Your INSYTMEDIA otp is ';

    /**
     * Create a new job instance.
     *
     * @param \App\Models\OneTimePassword $otp
     * @return void
     */
    public function __construct(OneTimePassword $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Only send SMS in production environment
        if (app()->environment('production')) {
            try {
                // Initialize the SDK
                $username = config('services.africastalking.username');
                $apiKey = config('services.africastalking.api_key');
                
                if (empty($username) || empty($apiKey)) {
                    Log::error('AfricasTalking credentials not configured');
                    return;
                }
                
                $AT = new AfricasTalking($username, $apiKey);
                
                // Get the SMS service
                $sms = $AT->sms();
                
                // Format phone number
                $phoneNumber = $this->formatPhoneNumber($this->otp->phone_number);
                
                // Send the message
                $message = $this->messagePrefix . $this->otp->otp_code;
                
                $result = $sms->send([
                    'to'      => $phoneNumber,
                    'message' => $message
                ]);
                
                Log::info('SMS sent successfully', [
                    'phone' => $phoneNumber,
                    'status' => $result['status']
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending SMS', [
                    'error' => $e->getMessage(),
                    'phone' => $this->otp->phone_number
                ]);
            }
        } else {
            // Log the OTP in non-production environments
            Log::info("OTP would be sent to {$this->otp->phone_number}: {$this->otp->otp_code}");
        }
    }
    
    /**
     * Format the phone number to international format for AfricasTalking.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 0, replace with +256 (Uganda)
        if (substr($phone, 0, 1) === '0') {
            return '+256' . substr($phone, 1);
        }
        
        // If it doesn't have a + prefix, add it
        if (substr($phone, 0, 1) !== '+') {
            return '+' . $phone;
        }
        
        return $phone;
    }
}
