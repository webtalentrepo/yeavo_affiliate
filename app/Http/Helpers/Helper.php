<?php
if (!function_exists('isSupperAdmin')) {
    function isSupperAdmin()
    {
        $currentUser = auth()->user();

        $admin = MyConfig::getValue('admin_email');
        if ($currentUser->email == $admin) {
            return true;
        }

        return false;
    }
}

if (!function_exists('checkSupperAdmin')) {
    function checkSupperAdmin($email)
    {
        $admin = MyConfig::getValue('admin_email');
        if ($email == $admin) {
            return true;
        }

        return false;
    }
}

if (!function_exists('isAdminUser')) {
    function isAdminUser()
    {
        if (auth()->user()->roles) {
            foreach (auth()->user()->roles as $row) {
                if ($row->is_admin) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('getConfig')) {
    function getConfig($config_key)
    {
        return MyConfig::getValue($config_key);
    }
}
