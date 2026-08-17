@extends('layouts.admin')

@section('title', 'تحويلات الروابط')
@section('subtitle', $redirects->total().' تحويل · '.$totalHits.' زيارة اتنقذت')

@section('actions')
    @if(auth()->user()->can_('seo.edit'))
        <button type="button" data-sheet-open="new-redirect" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">تحويل</span>
        </button>
    @endif
@endsection

@section('content')
    <div class="panel p-4">
        <p class="text-sm leading-[1.9] text-cream-2">
            لما تغيّر رابط مقال أو تمسحه، الرابط القديم بيبقى 404 — والزيارة اللي كانت جاية من جوجل بتضيع.
            التحويل هنا بيمسك الرابط القديم وبيوديه على الجديد بكود <span class="num">301</span>، فبتحافظ على ترتيبك.
        </p>
    </div>

    @if($redirects->isNotEmpty())
        <form method="GET" data-auto-filter class="mt-4">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="دوّر في الروابط…" class="field max-w-sm" aria-label="بحث في التحويلات">
        </form>
    @endif

    @if($redirects->isEmpty())
        <x-empty class="mt-6" title="مفيش تحويلات"
                 hint="أول ما تغيّر رابط مقال منشور، ارجع هنا وسجّل تحويل من الرابط القديم للجديد." />
    @else
        <div class="panel mt-4 overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr><th>من</th><th>إلى</th><th>الكود</th><th class="text-end">استُخدم</th><th>آخر مرة</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($redirects as $r)
                        <tr>
                            <td><span class="num text-xs" dir="ltr">{{ $r->from_path }}</span></td>
                            <td><span class="num text-xs" dir="ltr">{{ $r->to_path }}</span></td>
                            <td>
                                <span class="badge {{ $r->status_code === 301 ? 'badge-good' : ($r->status_code === 410 ? 'badge-bad' : 'badge-frost') }}">
                                    <span class="num">{{ $r->status_code }}</span>
                                </span>
                            </td>
                            <td class="num text-end">{{ $r->hits }}</td>
                            <td class="num whitespace-nowrap text-2xs text-cream-3">{{ $r->last_hit_at?->format('Y-m-d') ?: '—' }}</td>
                            <td class="text-end">
                                @if(auth()->user()->can_('seo.edit'))
                                    <form method="POST" action="{{ route('admin.redirects.destroy', $r) }}" data-confirm="هيتشال التحويل ده والرابط القديم هيرجع 404.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon text-cream-3" aria-label="امسح التحويل">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $redirects->links() }}</div>
    @endif
@endsection

@push('sheets')
    @if(auth()->user()->can_('seo.edit'))
        <dialog class="sheet" data-sheet="new-redirect" aria-labelledby="nr-h">
            <div class="sheet-panel">
                <div class="sheet-grip" aria-hidden="true"></div>
                <h2 id="nr-h" class="text-base font-semibold text-cream">تحويل جديد</h2>

                <form method="POST" action="{{ route('admin.redirects.store') }}" class="mt-5 flex flex-col gap-4">
                    @csrf
                    <div>
                        <label for="nr-from" class="label">الرابط القديم <span class="text-flame-ink">*</span></label>
                        <input id="nr-from" name="from_path" required dir="ltr" class="field num text-start" placeholder="/blog/old-slug" autofocus>
                        <p class="hint">المسار بس، من غير الدومين.</p>
                    </div>
                    <div>
                        <label for="nr-to" class="label">الرابط الجديد <span class="text-flame-ink">*</span></label>
                        <input id="nr-to" name="to_path" required dir="ltr" class="field num text-start" placeholder="/blog/new-slug">
                    </div>
                    <div>
                        <label for="nr-code" class="label">النوع</label>
                        <select id="nr-code" name="status_code" class="field">
                            <option value="301">301 — نقل دائم (المعتاد)</option>
                            <option value="302">302 — نقل مؤقت</option>
                            <option value="307">307 — مؤقت مع الحفاظ على الطريقة</option>
                            <option value="410">410 — اتشال نهائيًا</option>
                        </select>
                    </div>
                    <div class="flex gap-2.5 pt-1">
                        <button type="submit" class="btn btn-primary flex-1">فعّل</button>
                        <button type="button" data-sheet-close class="btn btn-ghost">إلغاء</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endpush
