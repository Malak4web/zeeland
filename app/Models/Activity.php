<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'description', 'properties', 'ip',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One line per meaningful change. This is the answer to "مين عدّل الطلب ده؟"
     * — without it a shared dashboard is unauditable.
     */
    public static function log(string $action, string $description, ?Model $subject = null, array $properties = []): void
    {
        static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => mb_substr($description, 0, 300),
            'properties' => $properties ?: null,
            'ip' => request()->ip(),
        ]);
    }
}
