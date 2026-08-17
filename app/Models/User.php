<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The four roles, in the order they appear in the UI.
     * admin sees everything; the rest see only what they are accountable for.
     */
    public const ROLES = [
        'admin' => 'مدير',
        'sales' => 'مبيعات',
        'accountant' => 'محاسب',
        'editor' => 'محرّر',
    ];

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * The single permission gate for the whole dashboard.
     *
     * Abilities are `area.action` strings. Keeping the matrix in one place means
     * a nav item, a route and a button all answer the same question the same way
     * — no drift between "can see the link" and "can do the thing".
     */
    public function can_(string $ability): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->role === 'admin') {
            return true;
        }

        $matrix = [
            'sales' => [
                'leads.view', 'leads.edit',
                'customers.view', 'customers.edit',
                'orders.view', 'orders.edit',
                'products.view',
                'payments.view',
                'reports.view',
            ],
            'accountant' => [
                'customers.view',
                'orders.view',
                'payments.view', 'payments.edit',
                'products.view',
                'reports.view',
                'leads.view',
            ],
            'editor' => [
                'blog.view', 'blog.edit',
                'seo.view', 'seo.edit',
            ],
        ];

        return in_array($ability, $matrix[$this->role] ?? [], true);
    }

    /** Where this user lands after login — never a page they cannot read. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'editor' => 'admin.posts.index',
            'accountant' => 'admin.payments.index',
            default => 'admin.dashboard',
        };
    }
}
