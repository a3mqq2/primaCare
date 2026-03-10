<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'center_id',
        'is_center_manager',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_center_manager' => 'boolean',
        ];
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function isSystemAdmin(): bool
    {
        return $this->role === 'system_admin';
    }

    public function isCenterManager(): bool
    {
        return $this->role === 'center_employee' && $this->is_center_manager;
    }

    public function isCenterEmployee(): bool
    {
        return $this->role === 'center_employee';
    }

    public function scopeVisibleTo($query, User $user)
    {
        if (!$user->isSystemAdmin()) {
            $query->where('center_id', $user->center_id);
        }
    }
}
