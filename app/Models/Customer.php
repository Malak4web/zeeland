<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'restaurant' => 'مطعم',
        'cafe' => 'كافيه',
        'hotel' => 'فندق',
        'distributor' => 'موزّع / جملة',
        'catering' => 'كاترينج',
        'market' => 'سوبرماركت',
        'other' => 'غير ده',
    ];

    protected $fillable = [
        'code', 'name', 'contact_name', 'business_type', 'phone', 'alt_phone',
        'email', 'governorate', 'address', 'tax_id', 'price_per_pack',
        'credit_limit', 'opening_balance', 'payment_terms_days', 'notes',
        'is_active', 'lead_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price_per_pack' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** Orders that actually move money. A draft or cancelled order does not. */
    public function billableOrders()
    {
        return $this->hasMany(Order::class)->whereIn('status', Order::BILLABLE);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->business_type] ?? ($this->business_type ?: '—');
    }

    /* --------------------------------------------------------------------
     | Money
     |
     | balance = opening + billed − paid.  Positive means the customer owes
     | us; negative means they are in credit (paid ahead).
     * ------------------------------------------------------------------ */

    public function totalBilled(): float
    {
        return (float) ($this->attributes['billed_sum']
            ?? $this->billableOrders()->sum('total'));
    }

    public function totalPaid(): float
    {
        return (float) ($this->attributes['paid_sum']
            ?? $this->payments()->sum('amount'));
    }

    public function balance(): float
    {
        return round((float) $this->opening_balance + $this->totalBilled() - $this->totalPaid(), 2);
    }

    public function overCreditLimit(): bool
    {
        return (float) $this->credit_limit > 0 && $this->balance() > (float) $this->credit_limit;
    }

    /**
     * One query that attaches billed/paid sums to a whole listing.
     * Without this the customers table would fire two queries per row.
     */
    public function scopeWithTotals(Builder $q): Builder
    {
        return $q->withSum(['billableOrders as billed_sum'], 'total')
            ->withSum(['payments as paid_sum'], 'amount');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('contact_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function whatsappUrl(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);
        if (str_starts_with($digits, '0')) {
            $digits = '20'.substr($digits, 1);
        }

        return "https://wa.me/{$digits}";
    }
}
