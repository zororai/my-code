<?php

namespace App\Http\Middleware;

use Closure;
use App\LoginAttempt;
use App\LoginLockout;
use App\User;
use App\Helpers\SmsHelper;

class LoginRateLimiter
{
    // Rate limits - more lenient for school environments
    const MAX_ATTEMPTS_PER_IP_PER_MINUTE = 30;  // Higher for shared school IPs
    const MAX_ATTEMPTS_PER_ACCOUNT_PER_HOUR = 15; // More forgiving for students

    public function handle($request, Closure $next)
    {
        // Only apply to POST login requests
        if ($request->isMethod('post') && $this->isLoginRoute($request)) {
            $ip = $request->ip();
            $email = $request->input('email');

            // Check if IP is locked
            if (LoginLockout::isIpLocked($ip)) {
                $lockoutTime = LoginLockout::getLockoutTime($ip);
                return $this->lockedResponse($request, $lockoutTime, 'ip');
            }

            // Check if account is locked
            if ($email && LoginLockout::isAccountLocked($email)) {
                $lockoutTime = LoginLockout::getLockoutTime(null, $email);
                return $this->lockedResponse($request, $lockoutTime, 'account');
            }

            // Check IP rate limit (5 per minute)
            $ipAttempts = LoginAttempt::getRecentFailedByIp($ip, 1);
            if ($ipAttempts >= self::MAX_ATTEMPTS_PER_IP_PER_MINUTE) {
                LoginLockout::lockIp($ip, 1, $ipAttempts, 'Rate limit exceeded');
                $this->logSecurityEvent($ip, $email, 'IP rate limit exceeded');
                return $this->rateLimitResponse($request);
            }

            // Check account rate limit (15 per hour)
            if ($email) {
                $accountAttempts = LoginAttempt::getRecentFailedByEmail($email, 1);
                if ($accountAttempts >= self::MAX_ATTEMPTS_PER_ACCOUNT_PER_HOUR) {
                    $lockoutMinutes = LoginLockout::getProgressiveLockoutMinutes($accountAttempts);
                    LoginLockout::lockAccount($email, $lockoutMinutes, $accountAttempts, 'Too many failed attempts');
                    $this->logSecurityEvent($ip, $email, 'Account rate limit exceeded');
                    
                    // Send SMS alert to all admins
                    $this->notifyAdminsViaSms($email, $ip, $accountAttempts);
                    
                    return $this->rateLimitResponse($request);
                }
            }

            // Apply progressive delay if needed (max 5 seconds to avoid timeout)
            $totalAttempts = LoginAttempt::getTotalFailedByIp($ip, 60);
            $delay = min(5, LoginLockout::getProgressiveDelay($totalAttempts));
            if ($delay > 0) {
                sleep($delay);
            }

            // Check if CAPTCHA is required
            $captchaRequired = LoginAttempt::requiresCaptcha($ip, $email);
            $request->merge(['_captcha_required' => $captchaRequired]);
        }

        return $next($request);
    }

    protected function isLoginRoute($request)
    {
        return $request->is('login') || $request->routeIs('login');
    }

    protected function lockedResponse($request, $lockoutTime, $type)
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

    protected function rateLimitResponse($request)
    {
        $message = 'Too many login attempts. Please wait before trying again.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 429);
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
     * Send SMS notification to all admin users when account rate limit is exceeded.
     */
    protected function notifyAdminsViaSms($email, $ip, $attemptCount)
    {
        try {
            // Get all admin users with phone numbers
            $admins = User::role('Admin')->whereNotNull('phone')->where('phone', '!=', '')->get();
            
            if ($admins->isEmpty()) {
                \Log::info('No admin users with phone numbers to notify about rate limit');
                return;
            }

            $message = "SECURITY ALERT: Account {$email} has been locked after {$attemptCount} failed login attempts from IP {$ip}. Time: " . now()->format('Y-m-d H:i:s');

            foreach ($admins as $admin) {
                $phone = $admin->phone;
                
                // Format phone number
                $phone = preg_replace('/\s+/', '', $phone);
                if (!preg_match('/^\+/', $phone)) {
                    $phone = '+263' . ltrim($phone, '0');
                }

                $result = SmsHelper::sendSms($phone, $message);
                
                if ($result['success']) {
                    \Log::info('Security alert SMS sent to admin', ['admin' => $admin->name, 'phone' => $phone]);
                } else {
                    \Log::warning('Failed to send security alert SMS to admin', ['admin' => $admin->name, 'error' => $result['message'] ?? 'Unknown']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error sending admin security alert SMS: ' . $e->getMessage());
        }
    }
}
