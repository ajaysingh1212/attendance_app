<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::audit('audit:created', $model);
        });

        static::updated(function (Model $model) {
            self::audit('audit:updated', $model, $model->getChanges());
        });

        static::deleted(function (Model $model) {
            self::audit('audit:deleted', $model);
        });
    }

    protected static function audit($description, $model, $changes = [])
    {
        if ($model instanceof AuditLog) {
            return;
        }

        $actor = auth()->user();
        $targetUser = self::targetUserForAudit($model);
        $action = Str::after($description, 'audit:');
        $properties = $action === 'updated'
            ? self::changedValuesForAudit($model, $changes)
            : self::snapshotForAudit($model);

        AuditLog::create([
            'description'  => $description,
            'action'       => $action,
            'module'       => self::moduleNameForAudit($model),
            'subject_id'   => $model->id ?? null,
            'subject_type' => get_class($model),
            'user_id'      => optional($actor)->id,
            'actor_name'   => self::displayNameForAudit($actor),
            'actor_role'   => self::roleNameForAudit($actor),
            'target_user_id'   => optional($targetUser)->id,
            'target_user_name' => self::displayNameForAudit($targetUser),
            'subject_name' => self::displayNameForAudit($model),
            'properties'   => $properties,
            'host'         => request()->ip(),
        ]);
    }

    protected static function changedValuesForAudit(Model $model, array $changes): array
    {
        unset($changes['updated_at']);

        return collect($changes)->map(function ($newValue, $field) use ($model) {
            return [
                'old' => $model->getOriginal($field),
                'new' => $newValue,
            ];
        })->all();
    }

    protected static function snapshotForAudit(Model $model): array
    {
        return collect($model->getAttributes())
            ->except(['password', 'remember_token'])
            ->all();
    }

    protected static function moduleNameForAudit(Model $model): string
    {
        $map = AuditLog::moduleOptions();

        return $map[class_basename($model)] ?? Str::headline(class_basename($model));
    }

    protected static function targetUserForAudit(Model $model): ?User
    {
        if ($model instanceof User) {
            return $model;
        }

        if (!empty($model->user_id)) {
            return User::find($model->user_id);
        }

        if (!empty($model->created_by_id)) {
            return User::find($model->created_by_id);
        }

        if (!empty($model->employee_id)) {
            $employee = Employee::find($model->employee_id);

            return $employee && $employee->user_id ? User::find($employee->user_id) : null;
        }

        return null;
    }

    protected static function displayNameForAudit($subject): ?string
    {
        if (!$subject) {
            return null;
        }

        foreach (['full_name', 'name', 'title', 'shop_name', 'order_id', 'employee_code', 'customer_code'] as $field) {
            if (!empty($subject->{$field})) {
                return $subject->{$field};
            }
        }

        return class_basename($subject) . ' #' . ($subject->id ?? '');
    }

    protected static function roleNameForAudit(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $user->roles()->pluck('title')->implode(', ') ?: null;
    }
}
