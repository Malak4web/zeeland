<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'status_code', 'hits', 'last_hit_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'last_hit_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** Always stored as a leading-slash path with no query and no trailing slash. */
    public static function normalise(string $path): string
    {
        $path = trim($path);
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $path = '/'.ltrim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
