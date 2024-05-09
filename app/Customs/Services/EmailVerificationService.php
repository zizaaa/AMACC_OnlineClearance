<?php

namespace App\Customs\Services;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\EmailVerification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class EmailVerificationService
{

    /**
     * Send verification link
     */
    public function sendVerificationLink(object $user): void
    {
        Notification::send($user, new EmailVerification($this->generateVerificationLink($user->email)));
    }

    /**
     * resend link with token
     */
    public function resendLink($email){
        $user = User::where('email', $email)->first();

            if($user){
                $this->sendVerificationLink($user);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Verification link sent successfully'
                ])->send();
            }else{
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ])->send();
            }
    }
    /**
     * check if user email is already verified
     */
    public function checkIfEmailIsVerified($user){
        if($user->email_verified_at){
            response()->json([
                'status' => 'failed',
                'message' => 'Email has already been verified'
            ])->send();
            exit;
        }
    }

    /**
     * verify user
     */
    public function verifyEmail(string $email, string $token){
        $user = User::where('email', $email)->first();
            if(!$user){
                response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ])->send();
                exit;
            }

            $this->checkIfEmailIsVerified($user);
            $verifiedToken = $this->verifyToken($email, $token);
                if($user->markEmailAsVerified()){
                    $verifiedToken ->delete();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Email has been verified successfully'
                    ]);
                }else{
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Emailverification failed'
                    ]);
                }
    }

    /**
     * verify token
     */
    public function verifyToken(string $email, string $token)
    {
        $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();

            if($token){
                if($token->expired_at >= now()){
                    return $token;
                }else{
                    $token->delete();
                    response()->json([
                        'status' => 'failed',
                        'message' => 'Token expired'
                    ])->send();
                    exit;
                }
            }else{
                response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid token'
                ])->send();
                exit;
            }
    }

    /**
     * generate verification link
     */
     public function generateVerificationLink(string $email): string
    {
        $checkIfTokenExist = EmailVerificationToken::where('email', $email)->first();
            if($checkIfTokenExist) $checkIfTokenExist->delete();

            $token = Str::uuid();
            $url = config('app.url'). "?token=". $token. "&email=". $email;
            $saveToken = EmailVerificationToken::create([
                "email"=> $email,
                "token"=> $token,
                "expired_at"=> now()->addMinutes(60),
            ]);

            if($saveToken){
                return $url;
            }
    }
}
