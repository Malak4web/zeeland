<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\Codes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('customer')->withPaid()
            ->search($request->query('q'))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('customer'), fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('order_date', '<=', $d))
            ->latest('order_date')->latest('id')
            ->paginate(20)->withQueryString();

        $unpaidFilter = $request->query('due') === '1';
        if ($unpaidFilter) {
            $orders->setCollection($orders->getCollection()->filter(fn (Order $o) => $o->dueAmount() > 0.005)->values());
        }

        $range = Order::query()
            ->whereIn('status', Order::BILLABLE)
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('order_date', '<=', $d))
            ->when($request->query('customer'), fn ($q, $c) => $q->where('customer_id', $c));

        return view('admin.orders.index', [
            'orders' => $orders,
            'rangeTotal' => (float) (clone $range)->sum('total'),
            'rangeCount' => (clone $range)->count(),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function create(Request $request)
    {
        $order = new Order([
            'order_date' => Carbon::today()->toDateString(),
            'status' => 'confirmed',
            'customer_id' => $request->query('customer'),
        ]);

        return view('admin.orders.form', $this->formData($order));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data) {
            $order = Order::create(collect($data)->except('items')->all() + [
                'code' => Codes::order((int) Carbon::parse($data['order_date'])->year),
                'created_by' => auth()->id(),
            ]);
            $this->syncItems($order, $data['items']);
            $order->recalculate();

            return $order;
        });

        Activity::log('created', "أنشأ طلب {$order->code} لـ {$order->customer->name}", $order);

        return redirect()->route('admin.orders.show', $order)->with('ok', 'الطلب اتسجّل.');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'payments', 'creator']);

        return view('admin.orders.show', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load(['customer', 'items']);

        return view('admin.orders.print', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load('items');

        return view('admin.orders.form', $this->formData($order));
    }

    public function update(Request $request, Order $order)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($order, $data) {
            $order->update(collect($data)->except('items')->all());
            $this->syncItems($order, $data['items']);
            $order->recalculate();
        });

        Activity::log('updated', "عدّل الطلب {$order->code}", $order);

        return redirect()->route('admin.orders.show', $order)->with('ok', 'اتحدّث.');
    }

    public function destroy(Order $order)
    {
        if ($order->payments()->exists()) {
            return back()->withErrors(['order' => 'الطلب ده عليه دفعات. الغيه بدل ما تمسحه.']);
        }

        $code = $order->code;
        $order->delete();
        Activity::log('deleted', "مسح الطلب {$code}", $order);

        return redirect()->route('admin.orders.index')->with('ok', 'اتمسح.');
    }

    private function formData(Order $order): array
    {
        return [
            'order' => $order,
            'customers' => Customer::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'code', 'price_per_pack', 'address']),
            'products' => Product::where('is_active', true)->orderBy('sort')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['required', 'string', 'max:160'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'customer_id.required' => 'اختار العميل.',
            'items.required' => 'الطلب لازم يكون فيه صنف واحد على الأقل.',
            'items.*.name.required' => 'اسم الصنف مطلوب.',
            'items.*.quantity.min' => 'الكمية لازم تكون أكبر من صفر.',
            'delivery_date.after_or_equal' => 'تاريخ التسليم مايكونش قبل تاريخ الطلب.',
        ]);

        $data['discount'] = $data['discount'] ?? 0;
        $data['shipping'] = $data['shipping'] ?? 0;

        return $data;
    }

    /**
     * Replace the lines wholesale. Editing an order is rare and the line count
     * is tiny, so a diff would add risk for no gain.
     */
    private function syncItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach (array_values($items) as $i => $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];

            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'],
                'unit' => $item['unit'] ?: 'شيكارة',
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => round($qty * $price, 2),
                'sort' => $i,
            ]);
        }
    }
}
