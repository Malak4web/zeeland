@extends('layouts.admin')

@section('title', 'المستخدمين')
@section('subtitle', $users->count().' حساب')

@section('actions')
    <button type="button" data-sheet-open="new-user" class="btn btn-primary btn-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        <span class="max-sm:sr-only">مستخدم</span>
    </button>
@endsection

@section('content')
    {{-- What each role can reach, spelled out — a permissions matrix nobody can
         read is a permissions matrix nobody trusts. --}}
    <section class="panel p-5" aria-labelledby="roles-h">
        <h2 id="roles-h" class="text-sm font-semibold text-cream">الأدوار</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                'مدير' => 'كل حاجة، بما فيها الإعدادات والمستخدمين وسجل النشاط.',
                'مبيعات' => 'طلبات الموقع، العملاء، الأوردرات، والتقارير. بيشوف الدفعات وماينفعش يعدّلها.',
                'محاسب' => 'الدفعات والتقارير وكشوف الحساب. بيشوف الأوردرات وماينفعش يعدّلها.',
                'محرّر' => 'المقالات والأقسام وتحويلات الروابط بس. مايشوفش أي أرقام حسابات.',
            ] as $role => $desc)
                <div class="rounded-xl border border-navy-2 p-3">
                    <dt class="text-sm font-medium text-cream">{{ $role }}</dt>
                    <dd class="mt-1 text-2xs leading-[1.8] text-cream-3">{{ $desc }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <ul class="mt-4 flex flex-col gap-3">
        @foreach($users as $user)
            <li class="panel p-4">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf @method('PATCH')

                    <div class="flex items-center gap-3 pb-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-navy-2 text-sm font-semibold text-cream">{{ mb_substr($user->name, 0, 1) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-cream">
                                {{ $user->name }}
                                @if($user->id === auth()->id())<span class="badge badge-flame ms-1.5">انت</span>@endif
                            </p>
                            <p class="text-2xs text-cream-3">
                                {{ $user->roleLabel() }}
                                @if($user->last_login_at) · آخر دخول {{ $user->last_login_at->diffForHumans(short: true) }}@endif
                            </p>
                        </div>
                        <span class="badge shrink-0 {{ $user->is_active ? 'badge-good' : 'badge-idle' }}">{{ $user->is_active ? 'نشط' : 'موقوف' }}</span>
                    </div>

                    <div class="grid gap-3 border-t border-navy-2 pt-4 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-3">
                            <label for="un-{{ $user->id }}" class="label">الاسم</label>
                            <input id="un-{{ $user->id }}" name="name" value="{{ $user->name }}" required class="field">
                        </div>
                        <div class="sm:col-span-3">
                            <label for="ue-{{ $user->id }}" class="label">الإيميل</label>
                            <input id="ue-{{ $user->id }}" name="email" type="email" dir="ltr" value="{{ $user->email }}" required class="field text-start text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="ur-{{ $user->id }}" class="label">الدور</label>
                            <select id="ur-{{ $user->id }}" name="role" class="field">
                                @foreach(\App\Models\User::ROLES as $key => $label)
                                    <option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="up-{{ $user->id }}" class="label">كلمة سر جديدة</label>
                            <input id="up-{{ $user->id }}" name="password" type="password" autocomplete="new-password" class="field text-xs" placeholder="سيبها فاضية">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="upc-{{ $user->id }}" class="label">تأكيد</label>
                            <input id="upc-{{ $user->id }}" name="password_confirmation" type="password" autocomplete="new-password" class="field text-xs">
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <label class="flex cursor-pointer items-center gap-2 py-1 text-xs text-cream-2">
                            <input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="size-4 accent-flame">
                            حساب نشط
                        </label>
                        <button type="submit" class="btn btn-solid btn-sm">احفظ</button>
                    </div>
                </form>

                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-3 border-t border-navy-2 pt-3"
                          data-confirm="هيتمسح حساب {{ $user->name }} نهائيًا. اللي عمله قبل كده بيفضل في سجل النشاط.">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">امسح الحساب</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
@endsection

@push('sheets')
    <dialog class="sheet" data-sheet="new-user" aria-labelledby="nu-h">
        <div class="sheet-panel">
            <div class="sheet-grip" aria-hidden="true"></div>
            <h2 id="nu-h" class="text-base font-semibold text-cream">مستخدم جديد</h2>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 flex flex-col gap-4">
                @csrf
                <div>
                    <label for="nu-name" class="label">الاسم <span class="text-flame-ink">*</span></label>
                    <input id="nu-name" name="name" required class="field" autofocus>
                </div>
                <div>
                    <label for="nu-email" class="label">الإيميل <span class="text-flame-ink">*</span></label>
                    <input id="nu-email" name="email" type="email" dir="ltr" required class="field text-start">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="nu-role" class="label">الدور</label>
                        <select id="nu-role" name="role" class="field">
                            @foreach(\App\Models\User::ROLES as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'sales')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nu-phone" class="label">الموبايل</label>
                        <input id="nu-phone" name="phone" dir="ltr" class="field num text-start">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="nu-pass" class="label">كلمة السر <span class="text-flame-ink">*</span></label>
                        <input id="nu-pass" name="password" type="password" autocomplete="new-password" required class="field">
                    </div>
                    <div>
                        <label for="nu-pass2" class="label">تأكيد</label>
                        <input id="nu-pass2" name="password_confirmation" type="password" autocomplete="new-password" required class="field">
                    </div>
                </div>
                <p class="hint">8 حروف على الأقل.</p>
                <div class="flex gap-2.5 pt-1">
                    <button type="submit" class="btn btn-primary flex-1">أضف</button>
                    <button type="button" data-sheet-close class="btn btn-ghost">إلغاء</button>
                </div>
            </form>
        </div>
    </dialog>
@endpush
