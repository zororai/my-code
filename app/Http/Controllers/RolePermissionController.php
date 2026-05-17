<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * ROLES CRUD
     */
    public function roles()
    {
        $roles = Role::with('permissions')->get();

        return view('backend.roles.index', compact('roles'));
    }

    public function createRole()
    {
        // Auto-sync sidebar permissions before showing the form
        $this->syncSidebarPermissions();
        
        $permissions = Permission::orderBy('name')->get();
        
        // Group sidebar permissions by category for better UX
        $groupedPermissions = $this->getGroupedSidebarPermissions();

        return view('backend.roles.create', compact('permissions', 'groupedPermissions'));
    }
    
    /**
     * Get sidebar permissions grouped by category
     */
    private function getGroupedSidebarPermissions()
    {
        return [
            'General' => [
                'sidebar-home' => 'Home',
                'sidebar-notifications' => 'Notifications'
            ],
            'Onboarding Process' => [
                'sidebar-onboard' => 'Onboarding Section',
                'sidebar-user-management' => 'User Management',
                'sidebar-teachers' => 'Teachers',
                'sidebar-students' => 'Students',
                'sidebar-subjects' => 'Subjects',
                'sidebar-assign-subjects' => 'Assign Subjects',
                'sidebar-classes' => 'Classes',
                'sidebar-parents' => 'Parents'
            ],
            'Student Management' => [
                'sidebar-student-section' => 'Student Section',
                'sidebar-student-record' => 'Student Record',
                'sidebar-seat-assignment' => 'Seat Assignment',
                'sidebar-applicants' => 'Applicants',
                'sidebar-disciplinary' => 'Disciplinary',
                'sidebar-medical-reports' => 'Medical Reports',
                'sidebar-library-records' => 'Library Records',
                'sidebar-results-management' => 'Results Management',
                'sidebar-results-approval' => 'Results Approval',
                'sidebar-assessment-approval' => 'Assessment Marks Approval',
                'sidebar-marking-scheme' => 'Marking Scheme',
                'sidebar-attendance' => 'Attendance',
                'sidebar-scholarships' => 'Scholarships'
            ],
            'School Staff' => [
                'sidebar-school-staff' => 'School Staff Section',
                'sidebar-staff-members' => 'Staff Members',
                'sidebar-logbook' => 'Logbook',
                'sidebar-attendance-scanner' => 'Attendance Scanner',
                'sidebar-attendance-history' => 'Attendance History',
                'sidebar-teacher-sessions' => 'Change Teacher Session',
                'sidebar-leave-management' => 'Leave Management',
                'sidebar-teacher-attendance' => 'Teacher Attendance',
                'sidebar-teacher-leave' => 'Teacher Leave',
                'sidebar-teacher-schemes' => 'Teacher Schemes',
                'sidebar-syllabus-topics' => 'Syllabus Topics',
                'view-teacher-device-info' => 'Manage Teacher Devices'
            ],
            'Timetable & Media' => [
                'sidebar-timetable' => 'Timetable',
                'sidebar-webcam' => 'Webcam'
            ],
            'Website Management' => [
                'sidebar-website' => 'Website Section',
                'sidebar-website-dashboard' => 'Website Dashboard',
                'sidebar-website-general' => 'General Settings',
                'sidebar-website-colors' => 'Theme Colors',
                'sidebar-website-images' => 'Images & Logo',
                'sidebar-website-text' => 'Text Content',
                'sidebar-website-landing-page' => 'Landing Page Settings',
                'sidebar-banner' => 'Banner',
                'sidebar-achievements' => 'Student Achievements',
                'sidebar-newsletter' => 'Newsletter',
                'sidebar-events' => 'Events'
            ],
            'Finance & Accounting' => [
                'sidebar-finance' => 'Finance Section',
                'sidebar-student-payments' => 'Student Payments',
                'sidebar-payment-verification' => 'Payment Verification',
                'sidebar-parents-arrears' => 'Parents with Arrears',
                'sidebar-school-income' => 'School Income',
                'sidebar-school-expenses' => 'School Expenses',
                'sidebar-inventory' => 'Inventory',
                'sidebar-pos' => 'POS / Sell',
                'sidebar-student-groceries' => 'Student Groceries',
                'sidebar-grocery-arrears' => 'Grocery Arrears',
                'sidebar-grocery-stock' => 'Grocery Stock',
                'sidebar-financial-statements' => 'Financial Statements',
                'sidebar-payroll' => 'Payroll',
                'sidebar-cashbook' => 'Cash Book',
                'sidebar-purchase-orders' => 'Purchase Orders',
                'sidebar-reports' => 'Reports & Dashboard',
                'sidebar-assets' => 'Asset Management',
                'assets-view' => 'View Assets',
                'assets-create' => 'Create Assets',
                'assets-edit' => 'Edit Assets',
                'assets-delete' => 'Delete Assets',
                'assets-assign' => 'Assign Assets',
                'assets-dispose' => 'Dispose Assets',
                'assets-maintenance' => 'Asset Maintenance',
                'assets-depreciation' => 'Asset Depreciation',
                'assets-reports' => 'Asset Reports',
                'asset-categories-manage' => 'Manage Asset Categories',
                'asset-locations-manage' => 'Manage Asset Locations',
                'sidebar-ledger' => 'Double-Entry Accounting & Ledger',
                'sidebar-financial-reports' => 'Financial Reports (Trial Balance, P&L, Balance Sheet)',
                'sidebar-journals' => 'General Journal',
                'sidebar-receivables' => 'Accounts Receivable',
                'sidebar-payables' => 'Accounts Payable'
            ],
            'Settings' => [
                'sidebar-settings' => 'Settings Section',
                'sidebar-settings-theme' => 'Theme Settings'
            ]
        ];
    }
    
    /**
     * Automatically scan sidebar and create missing permissions
     */
    private function syncSidebarPermissions()
    {
        $sidebarPath = resource_path('views/layouts/sidebar.blade.php');
        
        if (!file_exists($sidebarPath)) {
            return;
        }

        $content = file_get_contents($sidebarPath);
        
        // Find all @can('permission-name') directives
        preg_match_all("/@can\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches);
        
        $permissions = array_unique($matches[1]);
        
        // Add any hardcoded permissions that should always exist
        $additionalPermissions = [
            'sidebar-user-management',
            'sidebar-disciplinary',
            'sidebar-payroll',
            'sidebar-cashbook',
            'sidebar-purchase-orders',
            'sidebar-reports',
            'sidebar-inventory',
            'sidebar-pos',
            'sidebar-student-groceries',
            'sidebar-medical-reports',
            'sidebar-library-records',
            'sidebar-results-approval',
            'sidebar-assessment-approval',
            'sidebar-seat-assignment',
            'sidebar-assign-subjects',
            'sidebar-scholarships',
            'view-teacher-device-info',
            'sidebar-website-dashboard',
            'sidebar-website-general',
            'sidebar-website-colors',
            'sidebar-website-images',
            'sidebar-website-text',
            'sidebar-website-landing-page',
            'sidebar-banner',
            'sidebar-achievements',
            'sidebar-newsletter',
            'sidebar-events',
            'sidebar-payment-verification',
            'sidebar-teacher-attendance',
            'sidebar-teacher-leave',
            'sidebar-grocery-arrears',
            'sidebar-grocery-stock',
            'sidebar-financial-statements',
            'sidebar-assets',
            'assets-view',
            'assets-create',
            'assets-edit',
            'assets-delete',
            'assets-assign',
            'assets-dispose',
            'assets-maintenance',
            'assets-depreciation',
            'assets-reports',
            'asset-categories-manage',
            'asset-locations-manage',
            'sidebar-ledger',
            'sidebar-financial-reports',
            'sidebar-journals',
            'sidebar-receivables',
            'sidebar-payables',
            'sidebar-settings',
            'sidebar-settings-theme',
        ];
        
        $permissions = array_unique(array_merge($permissions, $additionalPermissions));

        foreach ($permissions as $permissionName) {
            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }
        
        // Remove obsolete permissions that no longer exist
        $this->removeObsoletePermissions();
    }
    
    /**
     * Remove permissions that no longer exist in the sidebar
     */
    private function removeObsoletePermissions()
    {
        $obsoletePermissions = [
            'sidebar-budgets', 
            'sidebar-reconciliation'
        ];
        
        foreach ($obsoletePermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                // Remove permission from all roles
                $permission->roles()->detach();
                // Delete the permission
                $permission->delete();
            }
        }
    }

    public function storeRole(Request $request)
    {
    	$request->validate([
            'name'	=> 'required|string|max:255|unique:roles'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->selectedpermissions);

        return redirect()->route('roles-permissions');
        
    }

    public function editRole($id)
    {
        // Auto-sync sidebar permissions before showing the form
        $this->syncSidebarPermissions();
        
        $role = Role::with('permissions')->find($id);
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->getGroupedSidebarPermissions();

        return view('backend.roles.edit', compact('role', 'permissions', 'groupedPermissions'));
    }

    public function updateRole(Request $request, $id)
    {
        // Sync permissions first to ensure all permissions exist
        $this->syncSidebarPermissions();
        
        $request->validate([
            'name'	=> 'required|string|max:255|unique:roles,name,'.$id
        ]);

    	$role = Role::findById($id); 
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->selectedpermissions);

        return redirect()->route('roles-permissions');
    }

    /**
     * PERMISSIONS CRUD
     */
    public function createPermission() 
    {
        $roles = Role::latest()->get();
        $permissions = Permission::latest()->get();

        return view('backend.permissions.create', compact('roles','permissions'));
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name'	=> 'required|string|max:255|unique:permissions'
        ]);

        $permission = Permission::create(['name' => $request->name]);
        $permission->assignRole($request->selectedroles);

        $roles = Role::latest()->get();
        $permissions = Permission::latest()->get();

        return view('backend.permissions.create', compact('roles','permissions'));
    }

    public function editPermission($id)
    {
        $permission = Permission::with('roles')->find($id);
        $roles = Role::latest()->get();

        return view('backend.permissions.edit', compact('roles','permission'));
    }

    public function updatePermission(Request $request, $id)
    {
        $request->validate([
            'name'	=> 'required|string|max:255|unique:permissions,name,'.$id
        ]);

    	$permission = Permission::findById($id); 
        $permission->update(['name' => $request->name]);
        $permission->syncRoles($request->selectedroles);

        return redirect()->route('roles-permissions');
    }

    /**
     * SIDEBAR PERMISSIONS MANAGEMENT
     */
    public function manageSidebarPermissions()
    {
        // Define all available sidebar items grouped by category
        $sidebarItems = [
            'Navigation' => [
                'sidebar-home' => 'Home/Dashboard',
                'sidebar-notifications' => 'Notifications'
            ],
            'User Management' => [
                'sidebar-onboard' => 'OnBoard Section',
                'sidebar-user-management' => 'User Management',
                'sidebar-teachers' => 'Teachers',
                'sidebar-students' => 'Students',
                'sidebar-subjects' => 'Subjects',
                'sidebar-assign-subjects' => 'Assign Subjects',
                'sidebar-classes' => 'Classes',
                'sidebar-parents' => 'Parents'
            ],
            'Student Management' => [
                'sidebar-student-section' => 'Student Section',
                'sidebar-student-record' => 'Student Record',
                'sidebar-seat-assignment' => 'Seat Assignment',
                'sidebar-applicants' => 'Applicants',
                'sidebar-disciplinary' => 'Disciplinary Records',
                'sidebar-medical-reports' => 'Student Medical Records',
                'sidebar-results-management' => 'Results Management',
                'sidebar-results-approval' => 'Results Approval',
                'sidebar-assessment-approval' => 'Assessment Marks Approval',
                'sidebar-marking-scheme' => 'Marking Scheme',
                'sidebar-attendance' => 'Attendance Register',
                'sidebar-scholarships' => 'Scholarships'
            ],
            'Staff Management' => [
                'sidebar-school-staff' => 'School Staff Section',
                'sidebar-staff-members' => 'Staff Members',
                'sidebar-logbook' => 'Teacher Log Book',
                'sidebar-attendance-scanner' => 'Attendance Scanner',
                'sidebar-attendance-history' => 'Attendance History',
                'sidebar-teacher-sessions' => 'Change Teacher Session',
                'sidebar-leave-management' => 'Leave Management',
                'sidebar-teacher-attendance' => 'Teacher - My Attendance',
                'sidebar-teacher-leave' => 'Teacher - Leave Applications',
                'sidebar-teacher-schemes' => 'Teacher Schemes',
                'sidebar-syllabus-topics' => 'Syllabus Topics',
                'sidebar-timetable' => 'Timetable',
                'sidebar-webcam' => 'Webcam',
                'view-teacher-device-info' => 'Manage Teacher Devices'
            ],
            'Website Management' => [
                'sidebar-website' => 'Website Section',
                'sidebar-website-dashboard' => 'Website Dashboard',
                'sidebar-website-general' => 'General Settings',
                'sidebar-website-colors' => 'Theme Colors',
                'sidebar-website-images' => 'Images & Logo',
                'sidebar-website-text' => 'Text Content',
                'sidebar-banner' => 'Banner',
                'sidebar-achievements' => 'Student Achievements',
                'sidebar-newsletter' => 'Newsletter',
                'sidebar-events' => 'Events'
            ],
            'Finance & Accounting' => [
                'sidebar-finance' => 'Finance & Accounting',
                'sidebar-student-payments' => 'Student Payments',
                'sidebar-payment-verification' => 'Payment Verification',
                'sidebar-parents-arrears' => 'Parents with Arrears',
                'sidebar-school-income' => 'School Income',
                'sidebar-school-expenses' => 'School Expenses',
                'sidebar-inventory' => 'Inventory',
                'sidebar-pos' => 'POS / Sell',
                'sidebar-student-groceries' => 'Student Groceries',
                'sidebar-grocery-arrears' => 'Grocery Arrears',
                'sidebar-grocery-stock' => 'Grocery Stock',
                'sidebar-financial-statements' => 'Financial Statements',
                'sidebar-payroll' => 'Payroll',
                'sidebar-cashbook' => 'Cash Book',
                'sidebar-purchase-orders' => 'Purchase Orders',
                'sidebar-reports' => 'Reports & Dashboard',
                'sidebar-assets' => 'Asset Management',
                'assets-view' => 'View Assets',
                'assets-create' => 'Create Assets',
                'assets-edit' => 'Edit Assets',
                'assets-delete' => 'Delete Assets',
                'assets-assign' => 'Assign Assets',
                'assets-dispose' => 'Dispose Assets',
                'assets-maintenance' => 'Asset Maintenance',
                'assets-depreciation' => 'Asset Depreciation',
                'assets-reports' => 'Asset Reports',
                'asset-categories-manage' => 'Manage Asset Categories',
                'asset-locations-manage' => 'Manage Asset Locations'
            ],
            'Settings' => [
                'sidebar-settings' => 'Settings Section'
            ]
        ];

        // Flatten the sidebar items to get all permission keys
        $allPermissions = [];
        foreach ($sidebarItems as $category => $items) {
            $allPermissions = array_merge($allPermissions, array_keys($items));
        }
        
        // Get all existing permissions
        $existingPermissions = Permission::whereIn('name', $allPermissions)->pluck('name')->toArray();
        
        // Create missing sidebar permissions
        foreach ($allPermissions as $permissionName) {
            if (!in_array($permissionName, $existingPermissions)) {
                Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // Get all users with roles
        $users = \App\User::with('permissions', 'roles.permissions')->get();
        
        return view('backend.permissions.sidebar', compact('sidebarItems', 'users'));
    }

    public function updateUserSidebarPermissions(Request $request)
    {
        $userId = $request->user_id;
        $permissions = $request->permissions ?? [];
        
        $user = \App\User::findOrFail($userId);
        
        // Define all sidebar permissions
        $sidebarPermissions = [
            'sidebar-home', 'sidebar-notifications', 'sidebar-onboard', 'sidebar-user-management', 'sidebar-teachers',
            'sidebar-students', 'sidebar-subjects', 'sidebar-assign-subjects', 'sidebar-classes', 'sidebar-parents',
            'sidebar-student-section', 'sidebar-student-record', 'sidebar-seat-assignment', 'sidebar-applicants',
            'sidebar-disciplinary', 'sidebar-medical-reports', 'sidebar-library-records', 'sidebar-results-management', 'sidebar-results-approval', 
            'sidebar-assessment-approval', 'sidebar-marking-scheme', 'sidebar-scholarships',
            'sidebar-attendance', 'sidebar-school-staff', 'sidebar-staff-members', 'sidebar-logbook',
            'sidebar-attendance-scanner', 'sidebar-attendance-history', 'sidebar-teacher-sessions',
            'sidebar-leave-management', 'sidebar-teacher-attendance', 'sidebar-teacher-leave',
            'sidebar-teacher-schemes', 'sidebar-syllabus-topics', 'view-teacher-device-info',
            'sidebar-timetable', 'sidebar-webcam', 'sidebar-website', 'sidebar-website-dashboard',
            'sidebar-website-general', 'sidebar-website-colors', 'sidebar-website-images', 'sidebar-website-text',
            'sidebar-banner', 'sidebar-achievements', 'sidebar-newsletter', 'sidebar-events', 'sidebar-finance', 'sidebar-student-payments',
            'sidebar-payment-verification', 'sidebar-parents-arrears', 'sidebar-school-income', 'sidebar-school-expenses',
            'sidebar-inventory', 'sidebar-pos', 'sidebar-student-groceries', 'sidebar-grocery-arrears',
            'sidebar-grocery-stock', 'sidebar-financial-statements', 'sidebar-payroll', 'sidebar-cashbook', 
            'sidebar-purchase-orders', 'sidebar-reports', 'sidebar-settings', 'sidebar-assets',
            'assets-view', 'assets-create', 'assets-edit', 'assets-delete', 'assets-assign',
            'assets-dispose', 'assets-maintenance', 'assets-depreciation', 'assets-reports',
            'asset-categories-manage', 'asset-locations-manage'
        ];
        
        // Remove all existing sidebar permissions from user
        $user->revokePermissionTo($sidebarPermissions);
        
        // Give selected permissions to user
        if (!empty($permissions)) {
            $user->givePermissionTo($permissions);
        }
        
        return redirect()->back()->with('success', 'Sidebar permissions updated successfully!');
    }
}
