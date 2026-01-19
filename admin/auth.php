<?php
/**
 * FoodFlow - Admin Authentication Helper
 */

session_start();

function requireAuth()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function getAdminName()
{
    return $_SESSION['admin_name'] ?? 'Admin';
}

function getAdminRole()
{
    return $_SESSION['admin_role'] ?? 'admin';
}

function isAdmin()
{
    return isset($_SESSION['admin_id']);
}

function logout()
{
    session_destroy();
    header('Location: login.php');
    exit;
}
