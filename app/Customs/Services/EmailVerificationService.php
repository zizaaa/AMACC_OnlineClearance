<?php

namespace App\Customs\Services;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class EmailVerificationService
{

    /**
     * Send verification link
     */
    public function sendVerificationLink(object $user): void
    {
        Notification::send($user, new EmailVerificationToken($this->generateVerifictionLink($user->email)));
    }

    /**
     * generate verification link
     */
     public function generateVerifictionLink(string $email): string
    {
        $checkIfTokenExist = EmailVerificationToken::where('email', $email)->first();
            if($checkIfTokenExist) $checkIfTokenExist->delete();

            $token = Str::uuid();
            $url = config('app.url'). "?token=". $token. "&email=". $email;
            $saveToken = EmailVerification::create([
                "email"=> $email,
                "token"=> $token,
                "expired_at"=> now()->addMinutes(60),
            ]);

            if($saveToken){
                return $url;
            }
    }
}
