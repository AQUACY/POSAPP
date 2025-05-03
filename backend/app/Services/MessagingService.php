<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class MessagingService
{
    protected $client;
    protected $fromNumber;
    protected $whatsappNumber;
    protected $isSandbox;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->fromNumber = config('services.twilio.from_number');
        $this->whatsappNumber = config('services.twilio.whatsapp_number');
        $this->isSandbox = config('services.twilio.sandbox_enabled', true);
    }

    public function sendOtp($phone, $otp)
    {
        try {
            // Format phone number to E.164 format
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            // Try WhatsApp first if enabled
            if ($this->isWhatsAppEnabled()) {
                try {
                    return $this->sendWhatsAppOtp($formattedPhone, $otp);
                } catch (\Exception $e) {
                    Log::warning('WhatsApp OTP failed, falling back to SMS: ' . $e->getMessage());
                    // Fall back to SMS if WhatsApp fails
                    return $this->sendSmsOtp($formattedPhone, $otp);
                }
            }
            
            // Send SMS if WhatsApp is not enabled
            return $this->sendSmsOtp($formattedPhone, $otp);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function sendWhatsAppOtp($phone, $otp)
    {
        // For sandbox, we need to use the sandbox number format
        if ($this->isSandbox) {
            $message = $this->client->messages->create(
                "whatsapp:" . $this->formatPhoneNumber($phone),
                [
                    'from' => "whatsapp:+14155238886", // Sandbox number
                    'body' => "Your POS System verification code is: {$otp}. This code will expire in 10 minutes."
                ]
            );
        } else {
            // For production, use the actual WhatsApp numbers
            $message = $this->client->messages->create(
                $this->formatWhatsAppNumber($phone),
                [
                    'from' => $this->formatWhatsAppNumber($this->whatsappNumber),
                    'body' => "Your POS System verification code is: {$otp}. This code will expire in 10 minutes."
                ]
            );
        }

        return $message->sid;
    }

    protected function sendSmsOtp($phone, $otp)
    {
        $message = $this->client->messages->create(
            $phone,
            [
                'from' => $this->fromNumber,
                'body' => "Your POS System verification code is: {$otp}. This code will expire in 10 minutes."
            ]
        );

        return $message->sid;
    }

    protected function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If the phone number already has a country code, return it as is
        if (str_starts_with($phone, '+')) {
            return $phone;
        }
        
        // Add country code if not present
        $phone = '+233' . $phone; // Default to Ghana
        
        return $phone;
    }

    protected function formatWhatsAppNumber($phone)
    {
        // Format for WhatsApp API
        return "whatsapp:" . $this->formatPhoneNumber($phone);
    }

    protected function isWhatsAppEnabled()
    {
        return config('services.twilio.whatsapp_enabled', true);
    }

    public function sendWhatsApp($phone, $message)
    {
        try {
            // Format phone number to E.164 format
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            // For sandbox, we need to use the sandbox number format
            if ($this->isSandbox) {
                $message = $this->client->messages->create(
                    "whatsapp:" . $formattedPhone,
                    [
                        'from' => "whatsapp:+14155238886", // Sandbox number
                        'body' => $message
                    ]
                );
            } else {
                // For production, use the actual WhatsApp numbers
                $message = $this->client->messages->create(
                    $this->formatWhatsAppNumber($phone),
                    [
                        'from' => $this->formatWhatsAppNumber($this->whatsappNumber),
                        'body' => $message
                    ]
                );
            }

            return $message->sid;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message: ' . $e->getMessage());
            throw $e;
        }
    }
} 