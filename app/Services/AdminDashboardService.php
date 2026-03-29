<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Invitation;

class AdminDashboardService
{
    public static function invitation()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return Invitation::query();
        }

        return Invitation::where('admin_id', auth()->user()->userable_id);
    }

    public static function getData()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return self::SuperAdminData();
        }

        return self::AdminData();
    }

    public static function SuperAdminData(): array
    {
        return [
            'invitationsCount' => self::invitationsCount(),
            'invitationsStats' => self::invitationsStats(),
            'invitations'      => self::getInvitations(),
            'admins'           => self::adminsCount(),
        ];
    }

    public static function AdminData(): array
    {
        return [
            'invitationsCount' => self::invitationsCount(),
            'invitationsStats' => self::invitationsStats(),
            'invitations'      => self::getInvitations(),
        ];
    }

    public static function getInvitations()
    {
        return self::invitation()->with('admin.user')->latest()->limit(20)->get();
    }

    public static function invitationsCount()
    {
        return self::invitation()?->count();
    }

    public static function invitationsStats()
    {
        return self::invitation()->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item): array => [$item->status => $item->count]);
    }

    public static function adminsCount()
    {
        return Admin::count();
    }
}
