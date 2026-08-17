<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * كشف حساب — one customer's ledger as a single ordered list.
 *
 * Rows are built from orders and payments rather than stored, so the statement
 * can never drift from the documents it reports on. A running balance is
 * carried forward: positive means the customer owes Zeeland.
 */
final class Statement
{
    public function __construct(
        public readonly Customer $customer,
        public readonly ?Carbon $from = null,
        public readonly ?Carbon $to = null,
    ) {}

    /**
     * Everything dated before `from` collapses into one opening line, so a
     * date-filtered statement still balances.
     */
    public function openingBalance(): float
    {
        $opening = (float) $this->customer->opening_balance;

        if (! $this->from) {
            return round($opening, 2);
        }

        $billed = $this->customer->orders()
            ->whereIn('status', Order::BILLABLE)
            ->whereDate('order_date', '<', $this->from)
            ->sum('total');

        $paid = $this->customer->payments()
            ->whereDate('paid_at', '<', $this->from)
            ->sum('amount');

        return round($opening + (float) $billed - (float) $paid, 2);
    }

    /** @return Collection<int, array{date: Carbon, type: string, ref: string, label: string, debit: float, credit: float, url: ?string, balance: float}> */
    public function rows(): Collection
    {
        $orders = $this->customer->orders()
            ->whereIn('status', Order::BILLABLE)
            ->when($this->from, fn ($q) => $q->whereDate('order_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('order_date', '<=', $this->to))
            ->with('items')
            ->get()
            ->map(fn (Order $o) => [
                'date' => $o->order_date,
                'type' => 'order',
                'ref' => $o->code,
                'label' => $this->describeOrder($o),
                'debit' => (float) $o->total,
                'credit' => 0.0,
                'url' => route('admin.orders.show', $o),
                'sort' => 0,
            ]);

        $payments = $this->customer->payments()
            ->when($this->from, fn ($q) => $q->whereDate('paid_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('paid_at', '<=', $this->to))
            ->get()
            ->map(fn ($p) => [
                'date' => $p->paid_at,
                'type' => 'payment',
                'ref' => $p->code,
                'label' => 'دفعة · '.$p->methodLabel().($p->reference ? ' · '.$p->reference : ''),
                'debit' => 0.0,
                'credit' => (float) $p->amount,
                'url' => route('admin.payments.index', ['q' => $p->code]),
                'sort' => 1,
            ]);

        $balance = $this->openingBalance();

        // A payment sorts after an order on the same day: money arrives against
        // something already invoiced, and the running balance should read that way.
        return $orders->concat($payments)
            ->sortBy([['date', 'asc'], ['sort', 'asc'], ['ref', 'asc']])
            ->values()
            ->map(function (array $row) use (&$balance) {
                $balance = round($balance + $row['debit'] - $row['credit'], 2);
                $row['balance'] = $balance;
                unset($row['sort']);

                return $row;
            });
    }

    public function totals(): array
    {
        $rows = $this->rows();

        return [
            'opening' => $this->openingBalance(),
            'debit' => round((float) $rows->sum('debit'), 2),
            'credit' => round((float) $rows->sum('credit'), 2),
            'closing' => $rows->isEmpty() ? $this->openingBalance() : (float) $rows->last()['balance'],
        ];
    }

    private function describeOrder(Order $order): string
    {
        $lines = $order->items;
        if ($lines->isEmpty()) {
            return 'طلب';
        }
        $first = $lines->first();
        $more = $lines->count() - 1;
        $text = Money::short($first->quantity).' '.$first->unit.' · '.$first->name;

        return $more > 0 ? $text." (+{$more})" : $text;
    }
}
