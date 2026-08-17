<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Payment;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDemo extends Command
{
    protected $signature = 'zeeland:clear-demo';

    protected $description = 'يمسح البيانات التجريبية اللي زرعها DemoSeeder (المعلّمة بـ «تجريبي»)';

    public function handle(): int
    {
        $mark = DemoSeeder::MARK;

        $customers = Customer::withTrashed()->where('name', 'like', $mark.'%')->pluck('id');
        $leads = Lead::withTrashed()->where('name', 'like', $mark.'%')->pluck('id');

        if ($customers->isEmpty() && $leads->isEmpty()) {
            $this->info('مفيش بيانات تجريبية.');

            return self::SUCCESS;
        }

        $this->warn("هيتمسح {$customers->count()} عميل و {$leads->count()} طلب موقع، بكل الأوردرات والدفعات بتاعتهم.");

        if (! $this->confirm('أكمّل؟', true)) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($customers, $leads) {
            $orders = Order::withTrashed()->whereIn('customer_id', $customers)->pluck('id');

            Payment::withTrashed()->whereIn('customer_id', $customers)->forceDelete();
            DB::table('order_items')->whereIn('order_id', $orders)->delete();
            Order::withTrashed()->whereIn('id', $orders)->forceDelete();
            Lead::withTrashed()->whereIn('id', $leads)->forceDelete();
            Customer::withTrashed()->whereIn('id', $customers)->forceDelete();
        });

        $this->info('اتمسحت.');

        return self::SUCCESS;
    }
}
