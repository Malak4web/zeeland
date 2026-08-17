<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'draft' => 'مسودة',
        'confirmed' => 'مؤكّد',
        'delivered' => 'اتسلّم',
        'cancelled' => 'ملغي',
    ];

    /** Statuses that count against the customer's account. */
    public const BILLABLE = ['confirmed', 'delivered'];

    protected $fillable = [
        'code', 'customer_id', 'order_date', 'delivery_date', 'status',
        'subtotal', 'discount', 'shipping', 'total',
        'delivery_address', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isBillable(): bool
    {
        return in_array($this->status, self::BILLABLE, true);
    }

    /** Recompute the money columns from the saved lines. Call after any edit. */
    public function recalculate(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $this->subtotal = $subtotal;
        $this->total = max(0, round($subtotal - (float) $this->discount + (float) $this->shipping, 2));
        $this->saveQuietly();
    }

    public function paidAmount(): float
    {
        return (float) ($this->attributes['paid_sum'] ?? $this->payments()->sum('amount'));
    }

    public function dueAmount(): float
    {
        return round((float) $this->total - $this->paidAmount(), 2);
    }

    /**
     * Payment state is derived, never stored — a stored copy is one more thing
     * that can disagree with the payments table.
     */
    public function paymentStatus(): string
    {
        if (! $this->isBillable()) {
            return 'none';
        }
        $due = $this->dueAmount();
        if ($due <= 0.005) {
            return 'paid';
        }

        return $this->paidAmount() > 0 ? 'partial' : 'unpaid';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->paymentStatus()) {
            'paid' => 'مدفوع',
            'partial' => 'مدفوع جزئي',
            'unpaid' => 'غير مدفوع',
            default => '—',
        };
    }

    public function totalKilos(): float
    {
        return (float) $this->items->sum(fn ($i) => (float) $i->quantity * (float) ($i->product->pack_size_kg ?? 0));
    }

    public function scopeWithPaid(Builder $q): Builder
    {
        return $q->withSum(['payments as paid_sum'], 'amount');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }

        return $q->where(function (Builder $q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
        });
    }
}
