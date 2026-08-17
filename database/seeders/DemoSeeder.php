<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\Codes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * ⚠ بيانات تجريبية — مش حقيقية.
 *
 * Opt-in only:  php artisan db:seed --class=DemoSeeder
 *
 * Every customer created here is prefixed «(تجريبي)» so it is obvious in the
 * lists, and `php artisan zeeland:clear-demo` removes the lot. Run it on an
 * empty install to see the dashboard with numbers in it, never on live books.
 */
class DemoSeeder extends Seeder
{
    public const MARK = '(تجريبي)';

    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $product = Product::first();

        if (! $product) {
            $this->command?->error('شغّل db:seed الأساسي الأول.');

            return;
        }

        $price = (float) ($product->price > 0 ? $product->price : 165);

        $seed = [
            ['مطعم الشرقاوي', 'restaurant', 'القاهرة', 'أحمد الشرقاوي', '01001234501', 40, 3],
            ['كافيه سنتر بوينت', 'cafe', 'الجيزة', 'مها فؤاد', '01001234502', 18, 4],
            ['فندق النيل بلازا', 'hotel', 'القاهرة', 'كريم منصور', '01001234503', 90, 2],
            ['مؤسسة البركة للتوزيع', 'distributor', 'الشرقية', 'سيد البركة', '01001234504', 220, 5],
            ['مطاعم بيت الفراخ', 'restaurant', 'الإسكندرية', 'ياسمين عادل', '01001234505', 60, 4],
            ['كاترينج المهندسين', 'catering', 'الجيزة', 'طارق سليم', '01001234506', 35, 2],
        ];

        foreach ($seed as $i => [$name, $type, $gov, $contact, $phone, $packs, $orderCount]) {
            $customer = Customer::create([
                'code' => Codes::customer(),
                'name' => self::MARK.' '.$name,
                'contact_name' => $contact,
                'business_type' => $type,
                'phone' => $phone,
                'governorate' => $gov,
                'price_per_pack' => round($price * (1 - min(0.12, $packs / 2200)), 2),
                'credit_limit' => $packs * $price * 1.5,
                'payment_terms_days' => $i % 2 === 0 ? 14 : 0,
                'is_active' => true,
                'created_by' => $admin?->id,
            ]);

            $paidRatio = [1.0, 1.0, 0.6, 0.35, 0.85, 0.0][$i];

            for ($n = 0; $n < $orderCount; $n++) {
                $date = Carbon::today()->subMonths($orderCount - $n)->subDays(($i * 3) % 20);
                $qty = max(4, $packs + (($i + $n) % 5) * 4 - 6);
                $unit = (float) $customer->price_per_pack;
                $line = round($qty * $unit, 2);

                $order = Order::create([
                    'code' => Codes::order((int) $date->year),
                    'customer_id' => $customer->id,
                    'order_date' => $date,
                    'delivery_date' => $date->copy()->addDays(2),
                    'status' => $n === $orderCount - 1 ? 'confirmed' : 'delivered',
                    'discount' => 0,
                    'shipping' => 0,
                    'created_by' => $admin?->id,
                ]);

                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'total' => $line,
                    'sort' => 0,
                ]);

                $order->recalculate();

                if ($paidRatio > 0) {
                    $amount = round($line * $paidRatio, 2);
                    Payment::create([
                        'code' => Codes::payment((int) $date->year),
                        'customer_id' => $customer->id,
                        'order_id' => $order->id,
                        'amount' => $amount,
                        'method' => ['cash', 'bank', 'instapay', 'wallet', 'cheque'][($i + $n) % 5],
                        'paid_at' => $date->copy()->addDays(($i % 3) + 1),
                        'created_by' => $admin?->id,
                    ]);
                }
            }
        }

        $leads = [
            ['محمود عبد الله', 'مطعم بيتزا رومانا', 'restaurant', '01112223301', 'القاهرة', '20–50 شيكارة', 'new'],
            ['نهى سمير', 'كافيه أوريجن', 'cafe', '01112223302', 'الجيزة', 'أقل من 20 شيكارة', 'contacted'],
            ['عمرو حسن', 'سوبرماركت الأمانة', 'market', '01112223303', 'المنوفية', '50–100 شيكارة', 'quoted'],
            ['رانيا فتحي', 'فندق سما', 'hotel', '01112223304', 'البحر الأحمر', 'أكتر من 100 شيكارة', 'new'],
            ['هشام لطفي', 'مطعم الدار', 'restaurant', '01112223305', 'أسيوط', '20–50 شيكارة', 'lost'],
        ];

        foreach ($leads as $i => [$name, $business, $type, $phone, $gov, $volume, $status]) {
            Lead::create([
                'name' => self::MARK.' '.$name,
                'business_name' => $business,
                'business_type' => $type,
                'phone' => $phone,
                'governorate' => $gov,
                'monthly_volume' => $volume,
                'message' => 'حابب أعرف السعر وشروط التوريد.',
                'status' => $status,
                'source' => 'landing_form',
                'contacted_at' => $status === 'new' ? null : now()->subDays($i + 1),
                'closed_at' => $status === 'lost' ? now()->subDays($i) : null,
                'created_at' => now()->subDays($i * 2 + 1),
            ]);
        }

        $this->command?->warn('اتزرعت بيانات تجريبية معلّمة بـ «'.self::MARK.'».');
        $this->command?->line('امسحها بـ:  php artisan zeeland:clear-demo');
    }
}
