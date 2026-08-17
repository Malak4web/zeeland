<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $billable = fn () => Order::query()->whereIn('status', Order::BILLABLE)
            ->whereBetween('order_date', [$from, $to]);

        $sold = (float) $billable()->sum('total');
        $orders = $billable()->count();
        $collected = (float) Payment::whereBetween('paid_at', [$from, $to])->sum('amount');

        // Sales by month across the chosen range.
        $byMonth = $billable()
            ->select(DB::raw("DATE_FORMAT(order_date, '%Y-%m') as ym"), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('ym')->orderBy('ym')->get();

        // Volume by product, in packs and in kilos.
        $byProduct = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', Order::BILLABLE)
            ->whereBetween('orders.order_date', [$from, $to])
            ->select(
                'order_items.name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.total) as total'),
            )
            ->groupBy('order_items.name')
            ->orderByDesc('total')
            ->get();

        $byCustomer = Order::query()
            ->whereIn('status', Order::BILLABLE)
            ->whereBetween('order_date', [$from, $to])
            ->select('customer_id', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('customer_id')->orderByDesc('total')->take(15)
            ->with('customer:id,name,code,phone')->get();

        $byGovernorate = Order::query()
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereIn('orders.status', Order::BILLABLE)
            ->whereNull('orders.deleted_at')
            ->whereBetween('orders.order_date', [$from, $to])
            ->select(DB::raw("COALESCE(customers.governorate, 'غير محدّد') as g"), DB::raw('SUM(orders.total) as total'))
            ->groupBy('g')->orderByDesc('total')->get();

        $leads = Lead::whereBetween('created_at', [$from, $to->copy()->endOfDay()]);
        $leadStats = [
            'total' => (clone $leads)->count(),
            'won' => (clone $leads)->where('status', 'won')->count(),
            'lost' => (clone $leads)->where('status', 'lost')->count(),
            'open' => (clone $leads)->whereIn('status', ['new', 'contacted', 'quoted'])->count(),
        ];
        $leadStats['rate'] = $leadStats['total'] > 0
            ? round($leadStats['won'] / $leadStats['total'] * 100)
            : 0;

        return view('admin.reports.index', compact(
            'from', 'to', 'sold', 'orders', 'collected',
            'byMonth', 'byProduct', 'byCustomer', 'byGovernorate', 'leadStats'
        ));
    }

    /**
     * Ageing receivables. Buckets are measured from the order date because that
     * is the only date every order has.
     */
    public function receivables()
    {
        $customers = Customer::query()->withTotals()->get()
            ->map(function (Customer $c) {
                $c->setAttribute('computed_balance', $c->balance());

                return $c;
            })
            ->filter(fn ($c) => $c->computed_balance > 0.005)
            ->sortByDesc('computed_balance')
            ->values();

        $ids = $customers->pluck('id');

        $unpaid = Order::query()
            ->whereIn('customer_id', $ids)
            ->whereIn('status', Order::BILLABLE)
            ->withPaid()->with('customer:id,name,code,phone,payment_terms_days')
            ->orderBy('order_date')
            ->get()
            ->filter(fn (Order $o) => $o->dueAmount() > 0.005);

        $buckets = ['current' => 0.0, 'd30' => 0.0, 'd60' => 0.0, 'd90' => 0.0];
        foreach ($unpaid as $order) {
            $age = $order->order_date->diffInDays(Carbon::today());
            $due = $order->dueAmount();
            match (true) {
                $age <= 30 => $buckets['current'] += $due,
                $age <= 60 => $buckets['d30'] += $due,
                $age <= 90 => $buckets['d60'] += $due,
                default => $buckets['d90'] += $due,
            };
        }

        return view('admin.reports.receivables', [
            'customers' => $customers,
            'unpaid' => $unpaid->values(),
            'buckets' => array_map(fn ($v) => round($v, 2), $buckets),
            'total' => round($customers->sum('computed_balance'), 2),
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(Request $request): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfYear();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return $from->lte($to) ? [$from, $to] : [$to, $from];
    }
}
