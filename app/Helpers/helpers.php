<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('checkRol')) {
    /**
     * Check if the authenticated user has a given role slug,
     * or has superadmin full access.
     *
     * @param string|array $roles
     * @return bool
     */
    function checkRol($roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // If user has hasRole method
        if (method_exists($user, 'hasRole')) {
            if (is_array($roles)) {
                return $user->hasAnyRole($roles);
            }
            return $user->hasRole($roles);
        }

        return false;
    }
}

if (!function_exists('currentUserDisplayName')) {
    /**
     * Get the display name of the currently authenticated user.
     *
     * @return string
     */
    function currentUserDisplayName(): string
    {
        if (!Auth::check()) {
            return 'Invitado';
        }

        $user = Auth::user();
        if (!empty($user->full_name)) {
            return $user->full_name;
        }
        if (!empty($user->nickname)) {
            return $user->nickname;
        }
        if (!empty($user->name)) {
            return $user->name;
        }
        return $user->email ?? 'Usuario';
    }
}

if (!function_exists('currentUserPrimaryRole')) {
    /**
     * Get the primary role name for the current authenticated user.
     *
     * @return string
     */
    function currentUserPrimaryRole(): string
    {
        if (!Auth::check()) {
            return 'Invitado';
        }

        $user = Auth::user();
        if (method_exists($user, 'getPrimaryRoleAttribute')) {
            return $user->primary_role;
        }

        return 'Usuario';
    }
}


if (!function_exists('getRoleRouteName')) {
    /**
     * Extract the role segment from a route name.
     * Given 'sica.admin.people.events_attendance.index' returns 'admin'.
     * Given 'sica.attendance.people.basic_data.search' returns 'attendance'.
     *
     * @param string $routeName
     * @return string
     */
    function getRoleRouteName(string $routeName): string
    {
        $parts = explode('.', $routeName);
        return $parts[1] ?? 'admin';
    }
}

if (!function_exists('getEnumValues')) {
    /**
     * Get the allowed values of an ENUM column from the database.
     *
     * @param string $table
     * @param string $column
     * @return array
     */
    function getEnumValues(string $table, string $column): array
    {
        try {
            $type = \DB::select(\DB::raw("SHOW COLUMNS FROM `{$table}` WHERE Field = '{$column}'"));
            if (empty($type)) {
                return [];
            }
            preg_match('/^enum\((.*)\)$/', $type[0]->Type, $matches);
            if (!isset($matches[1])) {
                return [];
            }
            $values = [];
            foreach (explode(',', $matches[1]) as $value) {
                $v = trim($value, "'");
                $values[$v] = $v;
            }
            return $values;
        } catch (\Exception $e) {
            return [];
        }
    }
}
