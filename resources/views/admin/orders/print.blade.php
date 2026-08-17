<!doctype html>
{{-- A printed document has one appearance. Pinning the token set here keeps
     paper light and ink dark no matter which theme the operator is using. --}}
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>إذن تسليم {{ $order->code }} — زيلاند</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@300;400;500;600&family=Martian+Mono:wght@400;500;600&display=swap">
    @vite('resources/js/admin/main.js')
</head>
{{-- A delivery note is a paper document that happens to render on screen: white
     ground, black ink, no dependency on colour to be readable. --}}
<body class="bg-paper text-abyss print:bg-white">

    <div class="mx-auto max-w-[820px] p-6 sm:p-10">

        <div class="no-print mb-6 flex flex-wrap gap-2">
            <button type="button" onclick="window.print()" class="rounded-lg bg-abyss px-4 py-2 text-sm font-medium text-cream">طباعة</button>
            <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-abyss/25 px-4 py-2 text-sm">رجوع</a>
        </div>

        <header class="flex items-start justify-between gap-6 border-b-2 border-abyss/80 pb-5">
            <div class="flex items-center gap-3">
                <svg width="44" height="44" viewBox="0 0 64 64" fill="none" aria-hidden="true" class="text-abyss">
                    <rect x="2.5" y="2.5" width="59" height="59" rx="17" stroke="currentColor" stroke-width="2.5"/>
                    <g fill="currentColor">
                        <path d="M32 23.5 20.2 14.1a1 1 0 0 1 1.3-1.5l11.2 9.6z"/><path d="M32 23.5 41.4 11.7a1 1 0 0 1 1.5 1.3l-9.6 11.2z"/>
                        <path d="M32 23.5 43.8 32.9a1 1 0 0 1-1.3 1.5l-11.2-9.6z"/><path d="M32 23.5 22.6 35.3a1 1 0 0 1-1.5-1.3l9.6-11.2z"/>
                    </g>
                    <circle cx="32" cy="23.5" r="2.6" fill="currentColor"/>
                    <path d="M28.4 27.4h7.2l3.1 15.4a1 1 0 0 1-1 1.2H26.3a1 1 0 0 1-1-1.2z" fill="currentColor"/>
                    <g stroke="currentColor" stroke-width="2.4" stroke-linecap="round" opacity="0.9"><path d="M16.5 48.5h31"/><path d="M13.5 53.5h37"/></g>
                </svg>
                <div>
                    <p class="text-xl font-semibold tracking-[0.14em]" style="direction:ltr">ZEELAND</p>
                    <p class="text-xs opacity-70">{{ $settings['site_tagline'] }}</p>
                </div>
            </div>
            <div class="text-end">
                <p class="text-base font-semibold">إذن تسليم</p>
                <p class="num mt-1 text-sm">{{ $order->code }}</p>
                <p class="num text-xs opacity-70">{{ $order->order_date->format('Y-m-d') }}</p>
            </div>
        </header>

        <section class="mt-6 grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold opacity-60">العميل</p>
                <p class="mt-1.5 text-base font-medium">{{ $order->customer?->name }}</p>
                <p class="num text-sm opacity-80">{{ $order->customer?->code }} · {{ $order->customer?->phone }}</p>
                @if($order->delivery_address ?: $order->customer?->address)
                    <p class="mt-1 text-sm opacity-80">{{ $order->delivery_address ?: $order->customer?->address }}</p>
                @endif
                @if($order->customer?->tax_id)
                    <p class="num mt-1 text-xs opacity-70">رقم ضريبي: {{ $order->customer->tax_id }}</p>
                @endif
            </div>
            <div class="sm:text-end">
                <p class="text-xs font-semibold opacity-60">المورّد</p>
                <p class="mt-1.5 text-base font-medium">{{ $settings['site_name'] }}</p>
                <p class="num text-sm opacity-80">{{ $settings['phone'] }}</p>
                @if($settings['email'])<p class="text-sm opacity-80" dir="ltr">{{ $settings['email'] }}</p>@endif
                @if($order->delivery_date)
                    <p class="num mt-1 text-xs opacity-70">تاريخ التسليم: {{ $order->delivery_date->format('Y-m-d') }}</p>
                @endif
            </div>
        </section>

        <table class="mt-8 w-full border-collapse text-sm">
            <thead>
                <tr class="border-b-2 border-abyss/70">
                    <th class="py-2 text-start text-xs font-semibold">#</th>
                    <th class="py-2 text-start text-xs font-semibold">الصنف</th>
                    <th class="py-2 text-end text-xs font-semibold">الكمية</th>
                    <th class="py-2 text-end text-xs font-semibold">السعر</th>
                    <th class="py-2 text-end text-xs font-semibold">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr class="border-b border-abyss/15">
                        <td class="num py-2.5">{{ $loop->iteration }}</td>
                        <td class="py-2.5">{{ $item->name }}</td>
                        <td class="num py-2.5 text-end">{{ \App\Support\Money::short($item->quantity) }} {{ $item->unit }}</td>
                        <td class="num py-2.5 text-end">{{ \App\Support\Money::format($item->unit_price) }}</td>
                        <td class="num py-2.5 text-end font-medium">{{ \App\Support\Money::format($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-5 flex justify-end">
            <dl class="w-full max-w-[19rem] text-sm">
                <div class="flex justify-between py-1"><dt class="opacity-70">مجموع الأصناف</dt><dd class="num">{{ \App\Support\Money::format($order->subtotal) }}</dd></div>
                @if($order->discount > 0)
                    <div class="flex justify-between py-1"><dt class="opacity-70">خصم</dt><dd class="num">− {{ \App\Support\Money::format($order->discount) }}</dd></div>
                @endif
                @if($order->shipping > 0)
                    <div class="flex justify-between py-1"><dt class="opacity-70">شحن</dt><dd class="num">+ {{ \App\Support\Money::format($order->shipping) }}</dd></div>
                @endif
                <div class="mt-2 flex justify-between border-t-2 border-abyss/70 pt-2 text-base font-semibold">
                    <dt>الإجمالي</dt>
                    <dd class="num">{{ \App\Support\Money::format($order->total) }} {{ $currency }}</dd>
                </div>
            </dl>
        </div>

        @if($order->notes)
            <p class="mt-6 border-t border-abyss/15 pt-4 text-sm opacity-80">{{ $order->notes }}</p>
        @endif

        <div class="mt-14 grid grid-cols-2 gap-10 text-sm">
            <div>
                <p class="border-t border-abyss/50 pt-2 opacity-70">توقيع المستلم</p>
            </div>
            <div>
                <p class="border-t border-abyss/50 pt-2 opacity-70">توقيع المندوب</p>
            </div>
        </div>

        <p class="mt-8 text-xs opacity-60">
            البضاعة تُحفظ على −18 درجة مئوية. الاستلام يعني الموافقة على الكميات والحالة الموضّحة أعلاه.
        </p>
    </div>
</body>
</html>
