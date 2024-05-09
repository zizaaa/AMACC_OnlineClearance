<?php

namespace App\Http\Controllers\Api\Auth;

use App\Customs\Services\EmailVerificationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private EmailVerificationService $service)
    {
        
    }
    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        $token = auth()->attempt($request->validated());
            if($token){
                return $this->responseWithToken($token, auth()->user());
            }else{
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid credentials'
                ],401);
            }
    }

    /**
     * resend verification link
     */
    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request){
        return $this->service->resendLink($request->email);
    }

    /**
     * verify user email
     */
    public function verifyUserEmail(VerifyEmailRequest $request){
        return $this->service->verifyEmail($request->email, $request->token);
    }

    /**
     * Registration
     */
    public function registration(RegistrationRequest $request)
    {
        $user = User::create($request->validated());
            
            if($user){
                $this->service->sendVerificationLink($user);
                $token = auth()->login($user);
                return $this->responseWithToken($token, $user);
            }else{
                return response()->json([
                    'status'=>'failed',
                    'message'=> 'An error occur while trying to create user'
                ],500);
            }
    }

    /**
     * return jwt token
     */
    public function responseWithToken($token, $user)
    {
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'access_token' => $token,
            'type' => 'bearer'
        ],201);
    }
}
