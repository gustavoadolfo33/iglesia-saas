<?php

namespace App\Support\Dashboard;

use App\Models\Church;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardContext
{
    public const SESSION_CHURCH_ID = 'dashboard.filters.church_id';
    public const SESSION_DATE_PRESET = 'dashboard.filters.date_preset';
    public const SESSION_DATE_FROM = 'dashboard.filters.date_from';
    public const SESSION_DATE_TO = 'dashboard.filters.date_to';

    public const PRESETS = [
        'today',
        'week',
        'month',
        'year',
        'custom',
    ];

    public static function resolve(?User $user = null): array
    {
        $user ??= auth()->user();

        if (!$user) {
            return static::defaults(null);
        }

        $filters = [
            'church_id' => session(self::SESSION_CHURCH_ID),
            'date_preset' => session(self::SESSION_DATE_PRESET),
            'date_from' => session(self::SESSION_DATE_FROM),
            'date_to' => session(self::SESSION_DATE_TO),
        ];

        return static::normalize($filters, $user);
    }

    public static function store(array $filters, ?User $user = null): array
    {
        $user ??= auth()->user();

        $resolved = static::normalize($filters, $user);

        session([
            self::SESSION_CHURCH_ID => $resolved['church_id'],
            self::SESSION_DATE_PRESET => $resolved['date_preset'],
            self::SESSION_DATE_FROM => $resolved['date_from'],
            self::SESSION_DATE_TO => $resolved['date_to'],
        ]);

        return $resolved;
    }

    public static function defaults(?User $user): array
    {
        [$dateFrom, $dateTo] = static::rangeForPreset('month');

        return [
            'church_id' => static::resolveDefaultChurchId($user),
            'date_preset' => 'month',
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ];
    }

    public static function normalize(array $filters, ?User $user): array
    {
        $defaults = static::defaults($user);
        $preset = in_array($filters['date_preset'] ?? null, self::PRESETS, true)
            ? $filters['date_preset']
            : $defaults['date_preset'];

        if ($preset === 'custom') {
            $dateFrom = static::parseDate($filters['date_from'] ?? null) ?? Carbon::parse($defaults['date_from']);
            $dateTo = static::parseDate($filters['date_to'] ?? null) ?? Carbon::parse($defaults['date_to']);

            if ($dateFrom->greaterThan($dateTo)) {
                [$dateFrom, $dateTo] = [$dateTo->copy(), $dateFrom->copy()];
            }
        } else {
            [$dateFrom, $dateTo] = static::rangeForPreset($preset);
        }

        return [
            'church_id' => static::resolveChurchId($filters['church_id'] ?? null, $user),
            'date_preset' => $preset,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ];
    }

    protected static function resolveChurchId(mixed $churchId, ?User $user): ?int
    {
        if (!$user) {
            return null;
        }

        if (!static::userCanChooseChurch($user)) {
            return $user->current_church_id ? (int) $user->current_church_id : null;
        }

        if ($churchId === null || $churchId === '' || $churchId === 'all') {
            return null;
        }

        $churchId = (int) $churchId;

        return Church::query()->whereKey($churchId)->exists() ? $churchId : null;
    }

    protected static function resolveDefaultChurchId(?User $user): ?int
    {
        if (!$user) {
            return null;
        }

        return static::userCanChooseChurch($user)
            ? null
            : ($user->current_church_id ? (int) $user->current_church_id : null);
    }

    protected static function userCanChooseChurch(User $user): bool
    {
        return $user->hasRole('super-admin') || $user->isGlobalUser();
    }

    protected static function parseDate(mixed $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function rangeForPreset(string $preset): array
    {
        $now = now();

        return match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }
}
