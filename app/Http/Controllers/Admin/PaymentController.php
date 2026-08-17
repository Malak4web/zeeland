<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Codes;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['customer', 'order', 'creator'])
            ->search($request->query('q'))
            ->when($request->query('method'), fn ($q, $m) => $q->where('method', $m))
            ->when($request->query('customer'), fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->latest('paid_at')->latest('id')
            ->paginate(25)->withQueryString();

        $range = Payment::query()
            ->when($request->query('method'), fn ($q, $m) => $q->where('method', $m))
            ->when($request->query('customer'), fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d));

        $byMethod = (clone $range)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')->pluck('total', 'method');

        return view('admin.payments.index', [
            'payments' => $payments,
            'rangeTotal' => (float) (clone $range)->sum('amount'),
            'byMethod' => $byMethod,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'code']),
            'openOrders' => $this->openOrders($request->query('customer')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $payment = DB::transaction(fn () => Payment::create($data + [
            'code' => Codes::payment((int) Carbon::parse($data['paid_at'])->year),
            'created_by' => auth()->id(),
        ]));

        Activity::log('created', "سجّل دفعة {$payment->code} بمبلغ ".Money::format($payment->amount), $payment);

        return back()->with('ok', 'الدفعة اتسجّلت.');
    }

    public function update(Request $request, Payment $payment)
    {
        $payment->update($this->validated($request));
        Activity::log('updated', "عدّل الدفعة {$payment->code}", $payment);

        return back()->with('ok', 'اتحدّثت.');
    }

    public function destroy(Payment $payment)
    {
        $code = $payment->code;
        $payment->delete();
        Activity::log('deleted', "مسح الدفعة {$code}", $payment);

        return back()->with('ok', 'اتمسحت.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:120'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_id.required' => 'اختار العميل.',
            'amount.required' => 'اكتب المبلغ.',
            'amount.min' => 'المبلغ لازم يكون أكبر من صفر.',
        ]);

        // A payment attached to another customer's order would silently corrupt
        // two statements at once.
        if (! empty($data['order_id'])) {
            $order = Order::find($data['order_id']);
            if (! $order || $order->customer_id !== (int) $data['customer_id']) {
                $data['order_id'] = null;
            }
        }

        return $data;
    }

    private function openOrders(?string $customerId)
    {
        return Order::query()
            ->whereIn('status', Order::BILLABLE)
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->withPaid()->with('customer')
            ->latest('order_date')->take(200)->get()
            ->filter(fn (Order $o) => $o->dueAmount() > 0.005)
            ->values();
    }
}
