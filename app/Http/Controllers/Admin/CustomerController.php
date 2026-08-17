<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Order;
use App\Support\Arabic;
use App\Support\Codes;
use App\Support\Statement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'name');

        $customers = Customer::query()
            ->withTotals()
            ->search($request->query('q'))
            ->when($request->query('type'), fn ($q, $t) => $q->where('business_type', $t))
            ->when($request->query('governorate'), fn ($q, $g) => $q->where('governorate', $g))
            ->when($request->query('state') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->query('state') !== 'inactive', fn ($q) => $q->where('is_active', true))
            ->orderBy($sort === 'newest' ? 'created_at' : 'name', $sort === 'newest' ? 'desc' : 'asc')
            ->paginate(20)
            ->withQueryString();

        // Debt sorting happens after paging because the balance is derived —
        // pushing it into SQL would mean duplicating the rule in two places.
        if ($sort === 'debt') {
            $customers->setCollection(
                $customers->getCollection()->sortByDesc(fn (Customer $c) => $c->balance())->values()
            );
        }

        return view('admin.customers.index', [
            'customers' => $customers,
            'governorates' => Customer::query()->whereNotNull('governorate')
                ->distinct()->orderBy('governorate')->pluck('governorate'),
        ]);
    }

    public function create()
    {
        return view('admin.customers.form', ['customer' => new Customer(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $customer = DB::transaction(fn () => Customer::create($data + [
            'code' => Codes::customer(),
            'created_by' => auth()->id(),
        ]));

        Activity::log('created', "أضاف عميل {$customer->code} — {$customer->name}", $customer);

        return redirect()->route('admin.customers.show', $customer)->with('ok', 'العميل اتسجّل.');
    }

    public function show(Customer $customer, Request $request)
    {
        $customer->load('lead');

        $orders = $customer->orders()->withPaid()->with('items')
            ->latest('order_date')->latest('id')->take(12)->get();

        $payments = $customer->payments()->latest('paid_at')->latest('id')->take(12)->get();

        $statement = new Statement($customer);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $orders,
            'payments' => $payments,
            'totals' => $statement->totals(),
            'monthly' => $this->monthlySpend($customer),
        ]);
    }

    public function statement(Customer $customer, Request $request)
    {
        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $statement = new Statement($customer, $from, $to);

        return view('admin.customers.statement', [
            'customer' => $customer,
            'rows' => $statement->rows(),
            'totals' => $statement->totals(),
            'from' => $from,
            'to' => $to,
            'print' => $request->boolean('print'),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer));
        Activity::log('updated', "عدّل بيانات {$customer->code} — {$customer->name}", $customer);

        return redirect()->route('admin.customers.show', $customer)->with('ok', 'اتحدّث.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->orders()->exists()) {
            return back()->withErrors(['customer' => 'مينفعش تمسح عميل عليه طلبات. وقّفه بدل ما تمسحه.']);
        }

        $customer->delete();
        Activity::log('deleted', "مسح العميل {$customer->code}", $customer);

        return redirect()->route('admin.customers.index')->with('ok', 'اتمسح.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $request->merge([
            'phone' => Arabic::digits((string) $request->input('phone')),
            'alt_phone' => Arabic::digits((string) $request->input('alt_phone')),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'business_type' => ['nullable', Rule::in(array_keys(Customer::TYPES))],
            'phone' => ['required', 'string', 'max:32'],
            'alt_phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:160'],
            'governorate' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'price_per_pack' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'opening_balance' => ['nullable', 'numeric', 'min:-99999999', 'max:99999999'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم المنشأة مطلوب.',
            'phone.required' => 'رقم الموبايل مطلوب.',
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    /** Last 6 months of billed value — the sparkline on the customer page. */
    private function monthlySpend(Customer $customer): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(5);
        $driver = DB::connection()->getDriverName();
        $dateSql = $driver === 'sqlite'
            ? "strftime('%Y-%m', order_date)"
            : "DATE_FORMAT(order_date, '%Y-%m')";

        $rows = $customer->orders()
            ->whereIn('status', Order::BILLABLE)
            ->where('order_date', '>=', $start)
            ->select(DB::raw("{$dateSql} as ym"), DB::raw('SUM(total) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $out = [];
        for ($i = 0; $i < 6; $i++) {
            $m = (clone $start)->addMonths($i);
            $out[] = [
                'label' => $m->translatedFormat('M'),
                'value' => round((float) ($rows[$m->format('Y-m')] ?? 0), 2),
            ];
        }

        return $out;
    }
}
