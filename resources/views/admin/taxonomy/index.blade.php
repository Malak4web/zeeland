@extends('layouts.admin')

@section('title', 'الأقسام والوسوم')
@section('back', route('admin.posts.index'))

@section('content')
    <div class="grid gap-4 lg:grid-cols-12">

        <div class="flex flex-col gap-4 lg:col-span-7">
            <section class="panel" aria-labelledby="cat-h">
                <div class="panel-head">
                    <h2 id="cat-h" class="panel-title">الأقسام</h2>
                    <button type="button" data-sheet-open="new-category" class="btn btn-ghost btn-sm">+ قسم</button>
                </div>

                @if($categories->isEmpty())
                    <x-empty class="m-4 border-0" title="مفيش أقسام"
                             hint="القسم بيعمل صفحة خاصة بيه على الموقع، وبيدخل في خريطة الموقع." />
                @else
                    <ul class="divide-y divide-navy-2">
                        @foreach($categories as $c)
                            <li class="p-4">
                                <form method="POST" action="{{ route('admin.categories.update', $c) }}">
                                    @csrf @method('PATCH')

                                    <div class="grid gap-3 sm:grid-cols-12 sm:items-end">
                                        <div class="sm:col-span-5">
                                            <label for="cn-{{ $c->id }}" class="label">الاسم</label>
                                            <input id="cn-{{ $c->id }}" name="name" value="{{ $c->name }}" required class="field">
                                        </div>
                                        <div class="sm:col-span-5">
                                            <label for="cs-{{ $c->id }}" class="label">الرابط</label>
                                            <input id="cs-{{ $c->id }}" name="slug" value="{{ $c->slug }}" class="field text-xs">
                                        </div>
                                        <div class="sm:col-span-2 flex gap-2">
                                            <input name="sort" type="number" min="0" value="{{ $c->sort }}" class="field num w-16 text-start" aria-label="الترتيب">
                                            <button type="submit" class="btn btn-solid btn-sm flex-1">احفظ</button>
                                        </div>
                                    </div>

                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-2xs text-cream-3 hover:text-cream-2">سيو القسم</summary>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <input name="meta_title" value="{{ $c->meta_title }}" class="field text-xs" placeholder="عنوان الميتا" aria-label="عنوان ميتا القسم">
                                            <input name="meta_description" value="{{ $c->meta_description }}" class="field text-xs" placeholder="وصف الميتا" aria-label="وصف ميتا القسم">
                                            <textarea name="description" rows="2" class="field sm:col-span-2 text-xs" aria-label="وصف القسم" placeholder="وصف بيظهر فوق قايمة مقالات القسم">{{ $c->description }}</textarea>
                                        </div>
                                    </details>
                                </form>

                                <div class="mt-3 flex items-center justify-between gap-3 border-t border-navy-2 pt-3">
                                    <p class="text-2xs text-cream-3"><span class="num">{{ $c->posts_count }}</span> مقال</p>
                                    @if($c->posts_count === 0)
                                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="هيتمسح قسم «{{ $c->name }}».">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">امسح</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <section class="panel lg:col-span-5" aria-labelledby="tag-h">
            <div class="panel-head">
                <h2 id="tag-h" class="panel-title">الوسوم</h2>
                <span class="num text-2xs text-cream-3">{{ $tags->count() }}</span>
            </div>

            <form method="POST" action="{{ route('admin.tags.store') }}" class="flex gap-2 border-b border-navy-2 p-4">
                @csrf
                <input name="name" required class="field flex-1" placeholder="وسم جديد" aria-label="اسم الوسم">
                <button type="submit" class="btn btn-solid btn-sm">أضف</button>
            </form>

            @if($tags->isEmpty())
                <x-empty class="m-4 border-0" title="مفيش وسوم" hint="الوسوم بتربط المقالات ببعضها وبتعمل صفحة لكل موضوع." />
            @else
                <ul class="flex flex-wrap gap-2 p-4">
                    @foreach($tags as $t)
                        <li class="flex items-center gap-2 rounded-full border border-navy-2 py-1 pe-1 ps-3 text-xs text-cream-2">
                            <span>{{ $t->name }}</span>
                            <span class="num text-2xs text-cream-3">{{ $t->posts_count }}</span>
                            <form method="POST" action="{{ route('admin.tags.destroy', $t) }}" data-confirm="هيتشال وسم «{{ $t->name }}» من كل المقالات.">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid size-6 place-items-center rounded-full text-cream-3 transition-colors hover:bg-bad/15 hover:text-bad" aria-label="امسح {{ $t->name }}">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection

@push('sheets')
    <dialog class="sheet" data-sheet="new-category" aria-labelledby="nc-h">
        <div class="sheet-panel">
            <div class="sheet-grip" aria-hidden="true"></div>
            <h2 id="nc-h" class="text-base font-semibold text-cream">قسم جديد</h2>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-5 flex flex-col gap-4">
                @csrf
                <div>
                    <label for="nc-name" class="label">الاسم <span class="text-flame-ink">*</span></label>
                    <input id="nc-name" name="name" required class="field" placeholder="القلي والتشغيل" autofocus>
                </div>
                <div>
                    <label for="nc-slug" class="label">الرابط</label>
                    <input id="nc-slug" name="slug" class="field" placeholder="بيتولّد من الاسم لو سبته فاضي">
                </div>
                <div>
                    <label for="nc-desc" class="label">الوصف</label>
                    <textarea id="nc-desc" name="description" rows="2" class="field"></textarea>
                </div>
                <div class="flex gap-2.5 pt-1">
                    <button type="submit" class="btn btn-primary flex-1">أضف</button>
                    <button type="button" data-sheet-close class="btn btn-ghost">إلغاء</button>
                </div>
            </form>
        </div>
    </dialog>
@endpush
