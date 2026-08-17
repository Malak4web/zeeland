<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $month = Carbon::now()->startOfMonth();
        $prevMonth = (clone $month)->subMonth();

        $sales = fn (Carbon $from, Carbon $to) => (float) Order::query()
            ->whereIn('status', Order::BILLABLE)
            ->whereBetween('order_date', [$from, $to])
            ->sum('total');

        $thisMonth = $sales($month, Carbon::now()->endOfMonth());
        $lastMonth = $sales($prevMonth, (clone $prevMonth)->endOfMonth());

        // Receivables: the number this dashboard exists for.
        $receivable = $this->outstandingTotal();

        $stats = [
            'sales_month' => $thisMonth,
            'sales_prev' => $lastMonth,
            'sales_delta' => $lastMonth > 0 ? round(($thisMonth - $lastMonth) / $lastMonth * 100) : null,
            'collected_month' => (float) Payment::whereBetween('paid_at', [$month, Carbon::now()->endOfMonth()])->sum('amount'),
            'receivable' => $receivable,
            'new_leads' => Lead::where('status', 'new')->count(),
            'open_leads' => Lead::whereIn('status', ['new', 'contacted', 'quoted'])->count(),
            'customers' => Customer::where('is_active', true)->count(),
            'orders_month' => Order::whereIn('status', Order::BILLABLE)
                ->whereBetween('order_date', [$month, Carbon::now()->endOfMonth()])->count(),
        ];

        // 12-month sales series for the inline chart.
        $series = $this->monthlySeries(12);

        $recentLeads = $user->can_('leads.view')
            ? Lead::with('assignee')->latest()->take(6)->get()
            : collect();

        $recentOrders = $user->can_('orders.view')
            ? Order::with('customer')->withPaid()->latest('order_date')->latest('id')->take(6)->get()
            : collect();

        $topDebtors = $user->can_('customers.view') ? $this->topDebtors(6) : collect();

        $draftPosts = $user->can_('blog.view')
            ? Post::whereIn('status', ['draft', 'scheduled'])->latest('updated_at')->take(5)->get()
            : collect();

        return view('admin.dashboard', compact(
            'stats', 'series', 'recentLeads', 'recentOrders', 'topDebtors', 'draftPosts'
        ));
    }

    /** Σ(opening) + Σ(billable orders) − Σ(payments), in three aggregate queries. */
    private function outstandingTotal(): float
    {
        $opening = (float) Customer::sum('opening_balance');
        $billed = (float) Order::whereIn('status', Order::BILLABLE)->sum('total');
        $paid = (float) Payment::sum('amount');

        return round($opening + $billed - $paid, 2);
    }

    private function monthlySeries(int $months): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $driver = DB::connection()->getDriverName();
        $dateSql = $driver === 'sqlite'
            ? "strftime('%Y-%m', order_date)"
            : "DATE_FORMAT(order_date, '%Y-%m')";

        $rows = Order::query()
            ->whereIn('status', Order::BILLABLE)
            ->where('order_date', '>=', $start)
            ->select(
                DB::raw("{$dateSql} as ym"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as orders'),
            )
            ->groupBy('ym')->pluck('total', 'ym');

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $m = (clone $start)->addMonths($i);
            $key = $m->format('Y-m');
            $series[] = [
                'key' => $key,
                'label' => $m->translatedFormat('M'),
                'short' => $m->format('n'),
                'value' => round((float) ($rows[$key] ?? 0), 2),
            ];
        }

        return $series;
    }

    /** @return \Illuminate\Support\Collection<int, Customer> */
    private function topDebtors(int $limit)
    {
        return Customer::query()
            ->withTotals()
            ->get()
            ->map(function (Customer $c) {
                $c->setAttribute('computed_balance', $c->balance());

                return $c;
            })
            ->filter(fn ($c) => $c->computed_balance > 0.005)
            ->sortByDesc('computed_balance')
            ->take($limit)
            ->values();
    }

    /**
     * The dashboard installs as its own app, separate from the public site —
     * different scope, different start URL, so "add to home screen" from the
     * dashboard never opens the landing page.
     */
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'زيلاند — لوحة التحكم',
            'short_name' => 'زيلاند إدارة',
            'lang' => 'ar',
            'dir' => 'rtl',
            'start_url' => route('admin.dashboard'),
            'scope' => url('/admin'),
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#010e1e',
            'theme_color' => '#010e1e',
            'icons' => [
                ['src' => asset('icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset('icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => asset('icon-512-maskable.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
