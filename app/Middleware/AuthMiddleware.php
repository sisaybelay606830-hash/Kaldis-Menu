<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\Response;

class AuthMiddleware
{
    /**
     * Require an authenticated admin session.
     * Redirects to /admin/login if not logged in.
     */
    public static function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please log in to access this area.');
            Response::redirect(base_url('admin/login'));
        }
    }

    /**
     * Require the 'administrator' role.
     * Calls requireAuth first, then checks the role.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (Session::userRole() !== 'administrator') {
            http_response_code(403);
            echo '<h1>403 — Forbidden</h1><p>Administrator access required.</p>';
            exit;
        }
    }

    /**
     * Require a guest (unauthenticated) session.
     * Redirects to dashboard if already logged in.
     */
    public static function requireGuest(): void
    {
        if (Session::isLoggedIn()) {
            Response::redirect(base_url('admin/dashboard'));
        }
    }
}

