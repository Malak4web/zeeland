@extends('layouts.admin')

@section('title', $lead->business_name ?: $lead->name)
@section('subtitle', 'طلب موقع #'.$lead->id.' · '.$lead->created_at->translatedFormat('j F Y — g:i a'))
@section('back', route('admin.leads.index'))

@section('actions')
    <a href="{{ $lead->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm">واتساب</a>
@endsection

@section('content')
    <div class="grid gap-4 lg:grid-cols-12">

        <div class="flex flex-col gap-4 lg:col-span-7">
            <section class="panel p-5" aria-labelledby="d-h">
                <h2 id="d-h" class="text-sm font-semibold text-cream">اللي كتبه في الفورم</h2>

                <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach([
                        ['الاسم', $lead->name, false],
                        ['المنشأة', $lead->business_name, false],
                        ['النشاط', $lead->business_type, false],
                        ['الموبايل', $lead->phone, true],
                        ['الإيميل', $lead->email, true],
                        ['المحافظة', $lead->governorate, false],
                        ['الكمية الشهرية', $lead->monthly_volume, false],
                        ['المصدر', $lead->source === 'landing_form' ? 'فورم الموقع' : $lead->source, false],
                    ] as [$label, $value, $mono])
                        <div>
                            <dt class="text-2xs text-cream-3">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm text-cream {{ $mono ? '' : '' }}">
                                @if($value)
                                    <span class="{{ $mono ? 'num' : '' }}">{{ $value }}</span>
                                @else
                                    <span class="text-cream-3">—</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>

                @if($lead->message)
                    <div class="mt-5 rounded-xl border border-navy-2 bg-navy/40 p-4">
                        <p class="text-2xs text-cream-3">الرسالة</p>
                        <p class="mt-1.5 text-sm leading-[1.9] text-cream-2">{{ $lead->message }}</p>
                    </div>
                @endif

                @if($lead->page_url || $lead->utm_source || $lead->referrer)
                    {{-- Attribution: this is how you learn which article pays for itself. --}}
                    <details class="mt-4">
                        <summary class="cursor-pointer text-2xs text-cream-3 transition-colors hover:text-cream-2">من فين جه الطلب</summary>
                        <dl class="mt-3 flex flex-col gap-2 text-2xs">
                            @foreach([
                                ['الصفحة', $lead->page_url],
                                ['جاي من', $lead->referrer],
                                ['utm_source', $lead->utm_source],
                                ['utm_medium', $lead->utm_medium],
                                ['utm_campaign', $lead->utm_campaign],
                                ['IP', $lead->ip],
                            ] as [$k, $v])
                                @if($v)
                                    <div class="flex gap-2">
                                        <dt class="w-24 shrink-0 text-cream-3">{{ $k }}</dt>
                                        <dd class="ltr-iso min-w-0 flex-1 truncate text-cream-2" dir="ltr">{{ $v }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </details>
                @endif
            </section>

            <section class="panel" aria-labelledby="n-h">
                <div class="panel-head">
                    <h2 id="n-h" class="panel-title">المتابعة</h2>
                    <span class="num text-2xs text-cream-3">{{ $lead->notes->count() }}</span>
                </div>

                @if(auth()->user()->can_('leads.edit'))
                    <form method="POST" action="{{ route('admin.leads.notes.store', $lead) }}" class="border-b border-navy-2 p-4">
                        @csrf
                        <label for="note" class="label">أضف ملاحظة</label>
                        <textarea id="note" name="body" rows="2" required class="field" placeholder="كلّمته، قال إنه…"></textarea>
                        <button type="submit" class="btn btn-solid btn-sm mt-3">سجّل</button>
                    </form>
                @endif

                @if($lead->notes->isEmpty())
                    <p class="px-4 py-8 text-center text-sm text-cream-3">مفيش متابعة لسه.</p>
                @else
                    <ol class="divide-y divide-navy-2">
                        @foreach($lead->notes as $note)
                            <li class="px-4 py-3">
                                <p class="text-sm leading-[1.9] text-cream-2">{{ $note->body }}</p>
                                <p class="mt-1 text-2xs text-cream-3">
                                    {{ $note->user?->name ?: 'النظام' }} · {{ $note->created_at->translatedFormat('j F — g:i a') }}
                                </p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        <div class="flex flex-col gap-4 lg:col-span-5">
            @if(auth()->user()->can_('leads.edit'))
                <section class="panel p-5" aria-labelledby="s-h">
                    <h2 id="s-h" class="text-sm font-semibold text-cream">الحالة</h2>

                    <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="mt-4 flex flex-col gap-4">
                        @csrf @method('PATCH')

                        <div>
                            <label for="status" class="label">وصل لفين</label>
                            <select id="status" name="status" class="field">
                                @foreach(\App\Models\Lead::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', $lead->status) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="assigned_to" class="label">المسؤول</label>
                            <select id="assigned_to" name="assigned_to" class="field">
                                <option value="">مش متعيّن</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('assigned_to', $lead->assigned_to) == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="lost_reason" class="label">سبب الخسارة <span class="text-cream-3">(لو ضاع)</span></label>
                            <input id="lost_reason" name="lost_reason" value="{{ old('lost_reason', $lead->lost_reason) }}" class="field" placeholder="السعر، التوقيت، مورّد تاني…">
                        </div>

                        <button type="submit" class="btn btn-primary">احفظ</button>
                    </form>

                    <dl class="mt-5 flex flex-col gap-2 border-t border-navy-2 pt-4 text-2xs">
                        <div class="flex justify-between"><dt class="text-cream-3">وصل</dt><dd class="text-cream-2">{{ $lead->created_at->translatedFormat('j F Y') }}</dd></div>
                        @if($lead->contacted_at)
                            <div class="flex justify-between"><dt class="text-cream-3">أول تواصل</dt><dd class="text-cream-2">{{ $lead->contacted_at->translatedFormat('j F Y') }}</dd></div>
                        @endif
                        @if($lead->closed_at)
                            <div class="flex justify-between"><dt class="text-cream-3">اتقفل</dt><dd class="text-cream-2">{{ $lead->closed_at->translatedFormat('j F Y') }}</dd></div>
                        @endif
                    </dl>
                </section>
            @endif

            <section class="panel p-5">
                @if($lead->customer)
                    <p class="text-sm text-cream">العميل ده متسجّل عندنا</p>
                    <a href="{{ route('admin.customers.show', $lead->customer) }}" class="btn btn-ghost mt-4 w-full">
                        افتح ملف <span class="num">{{ $lead->customer->code }}</span>
                    </a>
                @elseif(auth()->user()->can_('customers.edit'))
                    <p class="text-sm font-medium text-cream">اتفقنا معاه؟</p>
                    <p class="mt-1.5 text-2xs leading-[1.8] text-cream-3">هنعمله ملف عميل بنفس البيانات، ونعلّم الطلب ده إنه اتحوّل — من غير ما تكتب حاجة تاني.</p>
                    <form method="POST" action="{{ route('admin.leads.convert', $lead) }}" class="mt-4"
                          data-confirm="هيتعمل ملف عميل جديد باسم «{{ $lead->business_name ?: $lead->name }}» والطلب ده هيتعلّم إنه اتحوّل.">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">حوّله لعميل</button>
                    </form>
                @endif
            </section>

            @if(auth()->user()->can_('leads.edit'))
                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
                      data-confirm="الطلب ده هيتشال من القايمة. مش هينفع ترجعه من الواجهة.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-full">امسح الطلب</button>
                </form>
            @endif
        </div>
    </div>
@endsection
