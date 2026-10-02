<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Client => 'Client',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::SuperAdmin => 'admin.dashboard',
            self::Client => 'portal.dashboard',
        };
    }

    /**
     * @return list<string>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::SuperAdmin => [
                'clients.view', 'clients.create', 'clients.update', 'clients.delete',
                'contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete',
                'contacts.view_sensitive',
                'incidents.view', 'incidents.create', 'incidents.update', 'incidents.delete', 'incidents.assign',
                'users.view', 'users.create', 'users.update', 'users.delete',
                'audit.view', 'sessions.view_all', 'sessions.revoke_any',
            ],
            self::Client => [
                'contacts.view', 'contacts.create', 'contacts.update',
                'incidents.view', 'incidents.create',
            ],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
