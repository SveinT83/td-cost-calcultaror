<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCostCalculatorPermissions extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // Check if permissions table exists (assuming TaskHub uses a permissions system)
        if (Schema::hasTable('permissions')) {
            $permissionGroup = [
                'group_name' => 'Cost Calculator',
                'permissions' => [
                    [
                        'name' => 'view_cost_calculator',
                        'display_name' => 'View Cost Calculator',
                        'description' => 'Can view cost calculator dashboard and basic information'
                    ],
                    [
                        'name' => 'edit_cost_calculator',
                        'display_name' => 'Edit Cost Calculator',
                        'description' => 'Can add, edit and delete cost items and products'
                    ],
                    [
                        'name' => 'admin_cost_calculator',
                        'display_name' => 'Administer Cost Calculator',
                        'description' => 'Full administrative access to cost calculator module'
                    ],
                    [
                        'name' => 'api_access_cost_calculator',
                        'display_name' => 'API Access to Cost Calculator',
                        'description' => 'Can access cost calculator via API'
                    ],
                ]
            ];
            
            // Create permission group
            $groupId = null;
            
            // Check if table has 'group' column (it might vary depending on the permissions implementation)
            $hasGroupColumn = Schema::hasColumn('permissions', 'group');
            
            if ($hasGroupColumn) {
                // Get the column names from the permissions table to adapt to the schema
                $columns = Schema::getColumnListing('permissions');
                
                // Insert permissions with group
                foreach ($permissionGroup['permissions'] as $permission) {
                    $data = [
                        'name' => $permission['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Add optional columns if they exist in the schema
                    if (in_array('display_name', $columns)) {
                        $data['display_name'] = $permission['display_name'];
                    }
                    
                    if (in_array('description', $columns)) {
                        $data['description'] = $permission['description'];
                    }
                    
                    if (in_array('group', $columns)) {
                        $data['group'] = $permissionGroup['group_name'];
                    }
                    
                    DB::table('permissions')->insertOrIgnore($data);
                }
            } else {
                // Get the column names from the permissions table to adapt to the schema
                $columns = Schema::getColumnListing('permissions');
                
                // Just insert permissions without group
                foreach ($permissionGroup['permissions'] as $permission) {
                    $data = [
                        'name' => $permission['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Add optional columns if they exist in the schema
                    if (in_array('display_name', $columns)) {
                        $data['display_name'] = $permission['display_name'];
                    }
                    
                    if (in_array('description', $columns)) {
                        $data['description'] = $permission['description'];
                    }
                    
                    DB::table('permissions')->insertOrIgnore($data);
                }
            }
            
            // Add permissions to superadmin role if it exists
            $superadminRole = DB::table('roles')->where('name', 'superadmin')->first();
            
            if ($superadminRole) {
                $permissions = DB::table('permissions')
                    ->whereIn('name', array_map(function ($perm) {
                        return $perm['name'];
                    }, $permissionGroup['permissions']))
                    ->get();
                
                foreach ($permissions as $permission) {
                    // Check if the permissions_roles table exists (for Laravel Permission package compatibility)
                    if (Schema::hasTable('permission_role')) {
                        DB::table('permission_role')->insertOrIgnore([
                            'permission_id' => $permission->id,
                            'role_id' => $superadminRole->id,
                        ]);
                    }
                    // Check for standard Laravel 'role_has_permissions' table
                    else if (Schema::hasTable('role_has_permissions')) {
                        DB::table('role_has_permissions')->insertOrIgnore([
                            'permission_id' => $permission->id,
                            'role_id' => $superadminRole->id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('permissions')) {
            // Get all permission IDs for the group
            $permissionNames = [
                'view_cost_calculator',
                'edit_cost_calculator',
                'admin_cost_calculator',
                'api_access_cost_calculator'
            ];
            
            $permissions = DB::table('permissions')
                ->whereIn('name', $permissionNames)
                ->get();
                
            if ($permissions->count() > 0) {
                $permissionIds = $permissions->pluck('id')->toArray();
                
                // Remove from role_has_permissions if it exists
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();
                }
                
                // Remove from role_has_permissions if it exists
                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();
                }
                
                // Delete the permissions
                DB::table('permissions')
                    ->whereIn('id', $permissionIds)
                    ->delete();
            }
        }
    }
}
