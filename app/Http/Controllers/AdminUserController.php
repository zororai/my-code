<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\SchoolSetting;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    public function index()
    {
        // Exclude users who have Teacher, Student, or Parent roles, AND exclude super admin
        $excludedRoles = ['Teacher', 'Student', 'Parent'];
        
        $users = User::with('roles')
            ->where('is_super_admin', false) // Exclude super admin
            ->get()
            ->filter(function ($user) use ($excludedRoles) {
                // Keep user if they have at least one role AND don't have any of the excluded roles
                return $user->roles->isNotEmpty() && !$user->hasAnyRole($excludedRoles);
            });
        
        // Manual pagination
        $page = request()->get('page', 1);
        $perPage = 20;
        $total = $users->count();
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $users->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => request()->url()]
        );
        
        return view('backend.admin.users.index', compact('users'));
    }

    public function create()
    {
        // Get roles excluding Teacher, Student, Parent
        $roles = Role::whereNotIn('name', ['Teacher', 'Student', 'Parent'])->get();
        
        return view('backend.admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,name',
            'phone' => 'required|string|max:20',
        ]);

        // Default password for first login
        $defaultPassword = '12345678';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($defaultPassword),
            'phone' => $request->phone,
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        // Send SMS with credentials
        $this->sendCredentialsSms($request->phone, $user->name, $request->email, $defaultPassword);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully! Login credentials have been sent via SMS.');
    }

    /**
     * Send credentials SMS to new user.
     */
    private function sendCredentialsSms($phone, $name, $email, $password)
    {
        try {
            // Format phone number (ensure it has country code)
            $phone = preg_replace('/\s+/', '', $phone);
            if (!preg_match('/^\+/', $phone)) {
                $phone = '+263' . ltrim($phone, '0');
            }
            
            $messageTemplate = SchoolSetting::get(
                'sms_admin_user_credentials_template',
                'RSH School: Account created. Login: {email}, Password: {password}. Please change your password on first login.'
            );
            $message = str_replace(
                ['{name}', '{email}', '{password}'],
                [$name, $email, $password],
                $messageTemplate
            );
            
            // Send SMS using SmsHelper
            $result = \App\Helpers\SmsHelper::sendSms($phone, $message);
            
            if ($result['success']) {
                \Log::info('User credentials SMS sent successfully', [
                    'phone' => $phone,
                    'user_name' => $name
                ]);
            } else {
                \Log::warning('Failed to send user credentials SMS', [
                    'phone' => $phone,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send user credentials SMS: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);
        
        return view('backend.admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        
        // Prevent editing super admin
        if ($user->is_super_admin) {
            return redirect()->back()->with('error', 'Super Admin account cannot be edited.');
        }
        
        $roles = Role::whereNotIn('name', ['Teacher', 'Student', 'Parent'])->get();
        
        return view('backend.admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent updating super admin
        if ($user->is_super_admin) {
            return redirect()->back()->with('error', 'Super Admin account cannot be modified.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Sync role (remove old, add new)
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting super admin
        if ($user->is_super_admin) {
            return redirect()->back()->with('error', 'Super Admin account cannot be deleted.');
        }
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
