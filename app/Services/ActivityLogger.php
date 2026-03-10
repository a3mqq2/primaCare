<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(string $action, Model $model, ?array $oldValues = null): void
    {
        $properties = [];

        if ($action === 'updated' && $oldValues) {
            $changed = $model->getChanges();
            unset($changed['updated_at']);
            $properties['old'] = array_intersect_key($oldValues, $changed);
            $properties['new'] = $changed;
            $properties['changed_fields'] = array_keys($changed);
        } elseif ($action === 'deleted') {
            $attributes = $model->toArray();
            unset($attributes['password'], $attributes['remember_token']);
            $properties['deleted'] = $attributes;
        }

        $modelName = class_basename($model);
        $modelLabel = __('activity_logs.models.' . $modelName);
        $identifierValue = self::getModelIdentifier($model, $modelName);

        $descriptionKey = "activity_logs.description_{$action}";
        $modelDisplay = $identifierValue ? "{$modelLabel}: {$identifierValue}" : $modelLabel;
        $description = __($descriptionKey, ['model' => $modelDisplay]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelName,
            'model_id' => $model->id,
            'description' => $description,
            'properties' => !empty($properties) ? $properties : null,
            'ip_address' => request()->ip(),
        ]);
    }

    private static function getModelIdentifier(Model $model, string $modelName): ?string
    {
        return match ($modelName) {
            'User' => $model->name ?? null,
            'Center' => $model->name_ar ?? null,
            'City' => $model->name_ar ?? null,
            'Medicine' => $model->name ?? null,
            'MedicalRecord' => $model->full_name ?? null,
            'Dispensing' => $model->medicalRecord?->full_name ?? null,
            default => null,
        };
    }
}
