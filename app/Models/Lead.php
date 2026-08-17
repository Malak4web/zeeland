<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'new' => 'جديد',
        'contacted' => 'اتكلّمنا',
        'quoted' => 'اتبعت عرض',
        'won' => 'اتحوّل لعميل',
        'lost' => 'ضاع',
    ];

    protected $fillable = [
        'name', 'business_name', 'business_type', 'phone', 'email',
        'governorate', 'monthly_volume', 'message', 'status', 'assigned_to',
        'customer_id', 'source', 'contacted_at', 'closed_at', 'lost_reason',
        'page_url', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign',
        'ip', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['new', 'contacted', 'quoted'], true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('business_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /** wa.me deep link with the lead's own name already in the message. */
    public function whatsappUrl(): string
    {
        $digits = preg_replace('/\D+/', '', $this->phone);
        if (str_starts_with($digits, '0')) {
            $digits = '20'.substr($digits, 1);
        }
        $text = rawurlencode("أهلًا {$this->name} 👋\nمعاك زيلاند بخصوص طلب عرض سعر بطاطس نص مقلية.");

        return "https://wa.me/{$digits}?text={$text}";
    }
}
