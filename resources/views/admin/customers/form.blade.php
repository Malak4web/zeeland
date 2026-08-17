@extends('layouts.admin')

@section('title', $customer->exists ? 'تعديل '.$customer->name : 'عميل جديد')
@section('back', $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index'))

@section('content')
    <form method="POST"
          action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}"
          class="grid gap-4 lg:grid-cols-12">
        @csrf
        @if($customer->exists) @method('PATCH') @endif

        <div class="flex flex-col gap-4 lg:col-span-8">
            <section class="panel p-5" aria-labelledby="i-h">
                <h2 id="i-h" class="text-sm font-semibold text-cream">بيانات المنشأة</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">اسم المنشأة <span class="text-flame-ink">*</span></label>
                        <input id="name" name="name" value="{{ old('name', $customer->name) }}" required class="field"
                               placeholder="مطعم / كافيه / شركة توزيع…"
                               @if($errors->has('name')) aria-invalid="true" aria-describedby="name-e" @endif>
                        @error('name')<p id="name-e" class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="contact_name" class="label">المسؤول</label>
                        <input id="contact_name" name="contact_name" value="{{ old('contact_name', $customer->contact_name) }}" class="field" placeholder="اسم اللي بتتعامل معاه">
                    </div>

                    <div>
                        <label for="business_type" class="label">النشاط</label>
                        <select id="business_type" name="business_type" class="field">
                            <option value="">اختار…</option>
                            @foreach(\App\Models\Customer::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('business_type', $customer->business_type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="label">الموبايل <span class="text-flame-ink">*</span></label>
                        <input id="phone" name="phone" type="tel" inputmode="tel" dir="ltr" required
                               value="{{ old('phone', $customer->phone) }}" class="field num text-start" placeholder="01xxxxxxxxx"
                               @if($errors->has('phone')) aria-invalid="true" aria-describedby="phone-e" @endif>
                        @error('phone')<p id="phone-e" class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="alt_phone" class="label">رقم تاني</label>
                        <input id="alt_phone" name="alt_phone" type="tel" inputmode="tel" dir="ltr"
                               value="{{ old('alt_phone', $customer->alt_phone) }}" class="field num text-start">
                    </div>

                    <div>
                        <label for="email" class="label">الإيميل</label>
                        <input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $customer->email) }}" class="field text-start">
                    </div>

                    <div>
                        <label for="governorate" class="label">المحافظة</label>
                        <input id="governorate" name="governorate" value="{{ old('governorate', $customer->governorate) }}" class="field" list="gov-list">
                        <datalist id="gov-list">
                            @foreach(['القاهرة','الجيزة','الإسكندرية','القليوبية','الشرقية','الدقهلية','المنوفية','الغربية','البحيرة','كفر الشيخ','دمياط','بورسعيد','الإسماعيلية','السويس','شمال سيناء','جنوب سيناء','البحر الأحمر','مطروح','الفيوم','بني سويف','المنيا','أسيوط','سوهاج','قنا','الأقصر','أسوان','الوادي الجديد'] as $g)
                                <option value="{{ $g }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">العنوان</label>
                        <input id="address" name="address" value="{{ old('address', $customer->address) }}" class="field" placeholder="بيتحط تلقائيًا كعنوان تسليم في الأوردر">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">ملاحظات</label>
                        <textarea id="notes" name="notes" rows="3" class="field" placeholder="مواعيد التسليم المفضّلة، اشتراطات، أي حاجة تفرق">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <div class="flex flex-col gap-4 lg:col-span-4">
            <section class="panel p-5" aria-labelledby="t-h">
                <h2 id="t-h" class="text-sm font-semibold text-cream">شروط التعامل</h2>
                <p class="mt-1 text-2xs leading-[1.8] text-cream-3">القيم دي بتتحطّ تلقائيًا في أي أوردر جديد للعميل ده.</p>

                <div class="mt-4 flex flex-col gap-4">
                    <div>
                        <label for="price_per_pack" class="label">سعر الشيكارة ({{ $currency }})</label>
                        <input id="price_per_pack" name="price_per_pack" type="number" step="0.01" min="0" dir="ltr"
                               value="{{ old('price_per_pack', $customer->price_per_pack) }}" class="field num text-start">
                        <p class="hint">سيبه فاضي عشان ياخد سعر الصنف العادي.</p>
                    </div>

                    <div>
                        <label for="credit_limit" class="label">حد الائتمان ({{ $currency }})</label>
                        <input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" dir="ltr"
                               value="{{ old('credit_limit', $customer->credit_limit) }}" class="field num text-start">
                        <p class="hint">لو الرصيد عدّاه، هيتعلّم أحمر في القوايم.</p>
                    </div>

                    <div>
                        <label for="payment_terms_days" class="label">مهلة السداد (يوم)</label>
                        <input id="payment_terms_days" name="payment_terms_days" type="number" min="0" max="365" dir="ltr"
                               value="{{ old('payment_terms_days', $customer->payment_terms_days) }}" class="field num text-start">
                    </div>

                    <div>
                        <label for="opening_balance" class="label">رصيد افتتاحي ({{ $currency }})</label>
                        <input id="opening_balance" name="opening_balance" type="number" step="0.01" dir="ltr"
                               value="{{ old('opening_balance', $customer->opening_balance) }}" class="field num text-start"
                               @if($customer->exists) aria-describedby="ob-h" @endif>
                        <p id="ob-h" class="hint">
                            اللي كان عليه قبل ما تبدأ تستخدم النظام. موجب = عليه فلوس ليك.
                            @if($customer->exists)<strong class="text-warn">تعديله بيغيّر كشف الحساب كله.</strong>@endif
                        </p>
                    </div>

                    <div>
                        <label for="tax_id" class="label">الرقم الضريبي</label>
                        <input id="tax_id" name="tax_id" dir="ltr" value="{{ old('tax_id', $customer->tax_id) }}" class="field num text-start">
                    </div>

                    <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-navy-2 p-3">
                        <input type="checkbox" name="is_active" value="1" class="mt-1 size-4 accent-flame"
                               @checked(old('is_active', $customer->is_active ?? true))>
                        <span>
                            <span class="block text-sm text-cream">عميل نشط</span>
                            <span class="block text-2xs text-cream-3">العميل الموقوف بيختفي من القوايم بس حسابه بيفضل.</span>
                        </span>
                    </label>
                </div>
            </section>

            <div class="flex gap-2.5">
                <button type="submit" class="btn btn-primary flex-1">{{ $customer->exists ? 'احفظ التعديلات' : 'سجّل العميل' }}</button>
                <a href="{{ $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index') }}" class="btn btn-ghost">إلغاء</a>
            </div>
        </div>
    </form>

    @if($customer->exists && auth()->user()->can_('customers.edit'))
        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="mt-4 lg:max-w-xs"
              data-confirm="هيتمسح ملف «{{ $customer->name }}». ده بينفع بس لو ماعندوش أي أوردرات.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm w-full">امسح العميل</button>
        </form>
    @endif
@endsection
