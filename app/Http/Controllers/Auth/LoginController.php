<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\Teacher;
use App\Parents;
use App\LoginAttempt;
use App\LoginLockout;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('login.ratelimit')->only('login');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $ip = $request->ip();
        $email = $request->input('email');
        $userAgent = $request->userAgent();

        // Check if CAPTCHA is required and validate it
        $captchaRequired = $request->input('_captcha_required', false) || LoginAttempt::requiresCaptcha($ip, $email);
        
        if ($captchaRequired) {
            $captchaToken = $request->input('g-recaptcha-response');
            if (!$captchaToken || !$this->verifyCaptcha($captchaToken)) {
                LoginAttempt::logAttempt($ip, $email, $userAgent, false, 'CAPTCHA failed', true, false);
                return $this->sendCaptchaRequiredResponse($request);
            }
        }

        // Check for lockouts before attempting authentication
        if (LoginLockout::isIpLocked($ip) || LoginLockout::isAccountLocked($email)) {
            $lockoutTime = LoginLockout::getLockoutTime($ip, $email);
            return $this->sendLockoutResponse($request, $lockoutTime);
        }

        // Attempt login
        if ($this->attemptLogin($request)) {
            // Log successful attempt
            LoginAttempt::logAttempt($ip, $email, $userAgent, true, null, $captchaRequired, true);
            
            // Clear any lockouts on successful login
            LoginLockout::clearLockout($ip, $email);
            
            return $this->sendLoginResponse($request);
        }

        // Log failed attempt
        LoginAttempt::logAttempt($ip, $email, $userAgent, false, 'Invalid credentials', $captchaRequired, $captchaRequired);

        // Check if we need to apply lockout
        $failedAttempts = LoginAttempt::getRecentFailedByEmail($email, 1);
        if ($failedAttempts >= 10) {
            $lockoutMinutes = LoginLockout::getProgressiveLockoutMinutes($failedAttempts);
            LoginLockout::lockAccount($email, $lockoutMinutes, $failedAttempts, 'Too many failed attempts');
            
            $this->logSecurityEvent($ip, $email, 'Account locked after ' . $failedAttempts . ' failed attempts');
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function verifyCaptcha($token)
    {
        $secret = config('services.recaptcha.secret');
        
        if (!$secret) {
            // If reCAPTCHA is not configured, skip verification
            return true;
        }

        $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $token);
        
        if ($response === false) {
            return false;
        }

        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'] === true;
    }

    protected function sendCaptchaRequiredResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'CAPTCHA verification required.',
                'captcha_required' => true,
            ], 422);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['captcha' => 'Please complete the CAPTCHA verification.'])
            ->with('captcha_required', true);
    }

    protected function sendLockoutResponse(Request $request, $lockoutTime)
    {
        $minutes = now()->diffInMinutes($lockoutTime);
        $message = 'Too many login attempts. Please try again in ' . max(1, $minutes) . ' minute(s).';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'locked_until' => $lockoutTime->toIso8601String(),
            ], 429);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }

    protected function logSecurityEvent($ip, $email, $reason)
    {
        \Log::warning('Login security event', [
            'ip' => $ip,
            'email' => $email,
            'reason' => $reason,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $login = $request->input('email');
        $password = $request->input('password');

        // First try to login with email
        if (Auth::attempt(['email' => $login, 'password' => $password, 'is_active' => true])) {
            return true;
        }

        // Try to find user by phone number directly in users table (for admin users and others)
        $user = User::where('phone', $login)->first();
        if ($user && $user->is_active && Auth::attempt(['email' => $user->email, 'password' => $password, 'is_active' => true])) {
            return true;
        }

        // If direct phone login fails, try to find user by phone number (for teachers)
        $teacher = Teacher::where('phone', $login)->first();
        if ($teacher) {
            $user = User::find($teacher->user_id);
            if ($user && $user->is_active && Auth::attempt(['email' => $user->email, 'password' => $password, 'is_active' => true])) {
                return true;
            }
        }

        // If teacher phone login fails, try to find user by phone number (for parents)
        $parent = Parents::where('phone', $login)->first();
        if ($parent) {
            $user = User::find($parent->user_id);
            if ($user && $user->is_active && Auth::attempt(['email' => $user->email, 'password' => $password, 'is_active' => true])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->{$this->username()},
            'password' => $request->password,
            'is_active' => true,
        ];
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
