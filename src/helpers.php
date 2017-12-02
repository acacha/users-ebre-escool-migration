<?php

use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

if (! function_exists('permission_first_or_create')) {
    /**
     * Create permission by name or retrieve permission if already exists.
     *
     * @param $permission
     * @return \Illuminate\Database\Eloquent\Model|\Spatie\Permission\Contracts\Permission
     */
    function permission_first_or_create($permission)
    {
        try {
            return Permission::create(['name' => $permission]);
        } catch (PermissionAlreadyExists $e) {
            return Permission::findByName($permission);
        }
    }
}

if (! function_exists('give_permission_to_role')) {
    /**
     * Give permission to role.
     *
     * @param $role
     * @param $permission
     */
    function give_permission_to_role($role,$permission)
    {
        try {
            $role->givePermissionTo($permission);
        } catch (Illuminate\Database\QueryException $e) {
            info('Permissions ' . $permission . ' already assigned to role ' . $role->name);
        }
    }
}

if (! function_exists('role_first_or_create')) {
    /**
     * Create  role by name or retrieve role if already exists.
     *
     * @param $role
     * @return \Illuminate\Database\Eloquent\Model|\Spatie\Permission\Contracts\Role|Role
     */
    function role_first_or_create($role)
    {
        try {
            return Role::create(['name' => $role]);
        } catch (RoleAlreadyExists $e) {
            return Role::findByName($role);
        }
    }
}

if (! function_exists('initialize_users_management_migration_permissions')) {
    /**
     * Initialize users managment migration permissions.
     */
        function initialize_users_management_migration_permissions() {
        $migrateUsers = role_first_or_create('migrate-users');
        permission_first_or_create('migrate-users');
        give_permission_to_role($migrateUsers,'migrate-users');
    }
}
