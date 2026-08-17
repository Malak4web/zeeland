@extends('layouts.admin')

@section('title', $post->exists ? 'تعديل المقال' : 'مقال جديد')
@section('subtitle', $post->exists ? $post->title : 'اكتب، وشوف درجة السيو بتتحرك وانت بتكتب')
@section('back', route('admin.posts.index'))

@section('actions')
    @if($post->exists && $post->isLive())
        <a href="{{ $post->url() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">عرض ↗</a>
    @endif
    <button type="submit" form="post-form" class="btn btn-primary btn-sm">حفظ</button>
@endsection

@section('content')
    <form id="post-form" method="POST" data-post-form
          action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
          class="grid gap-4 lg:grid-cols-12">
        @csrf
        @if($post->exists) @method('PATCH') @endif

        {{-- ─────────────────────────────────────────────── writing surface --}}
        <div class="flex flex-col gap-4 lg:col-span-8">
            <section class="panel p-5">
                <label for="title" class="label">العنوان <span class="text-flame-ink">*</span></label>
                <input id="title" name="title" required
                       value="{{ old('title', $post->title) }}" class="field text-lg"
                       placeholder="اكتب العنوان اللي الناس هتشوفه في جوجل"
                       @if($errors->has('title')) aria-invalid="true" @endif>
                @error('title')<p class="error">{{ $message }}</p>@enderror

                <div class="mt-4">
                    <label for="excerpt" class="label">المقتطف</label>
                    <textarea id="excerpt" name="excerpt" rows="2" class="field"
                              placeholder="سطرين بيظهروا في قايمة المدوّنة">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
            </section>

            {{-- The editor: a contenteditable surface writing into a real
                 textarea, so nothing depends on JS surviving to save. --}}
            <section class="panel" data-editor data-upload-url="{{ route('admin.media.store') }}" aria-labelledby="ed-h">
                <div class="panel-head">
                    <h2 id="ed-h" class="panel-title">المقال</h2>
                    <p class="text-2xs text-cream-3"><span class="num" data-editor-count>0</span> كلمة</p>
                </div>

                <div data-editor-toolbar class="flex flex-wrap items-center gap-1 border-b border-navy-2 p-2">
                    <select data-block-select class="field btn-sm max-w-[8.5rem] text-xs" aria-label="نوع الفقرة"></select>

                    <span class="mx-1 h-5 w-px bg-navy-2" aria-hidden="true"></span>

                    <button type="button" data-cmd="bold" class="btn btn-ghost btn-sm btn-icon font-bold" aria-label="عريض" title="عريض">B</button>
                    <button type="button" data-cmd="italic" class="btn btn-ghost btn-sm btn-icon italic" aria-label="مائل" title="مائل">I</button>

                    <span class="mx-1 h-5 w-px bg-navy-2" aria-hidden="true"></span>

                    <button type="button" data-cmd="insertUnorderedList" class="btn btn-ghost btn-sm btn-icon" aria-label="قائمة نقطية" title="قائمة نقطية">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1.2" fill="currentColor"/><circle cx="4" cy="12" r="1.2" fill="currentColor"/><circle cx="4" cy="18" r="1.2" fill="currentColor"/></svg>
                    </button>
                    <button type="button" data-cmd="insertOrderedList" class="btn btn-ghost btn-sm btn-icon" aria-label="قائمة مرقّمة" title="قائمة مرقّمة">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M9 6h12M9 12h12M9 18h12M4 5h1v4M4 13h2l-2 3h2"/></svg>
                    </button>
                    <button type="button" data-cmd="quote" class="btn btn-ghost btn-sm btn-icon" aria-label="اقتباس" title="اقتباس">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 15a3 3 0 1 1 0-6c0-3 2-4 2-4M17 15a3 3 0 1 1 0-6c0-3 2-4 2-4"/></svg>
                    </button>

                    <span class="mx-1 h-5 w-px bg-navy-2" aria-hidden="true"></span>

                    <button type="button" data-cmd="link" class="btn btn-ghost btn-sm btn-icon" aria-label="رابط" title="رابط">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 13.5a4 4 0 0 0 5.7 0l3-3a4 4 0 1 0-5.7-5.7l-1.7 1.7"/><path d="M13.5 10.5a4 4 0 0 0-5.7 0l-3 3a4 4 0 1 0 5.7 5.7l1.7-1.7"/></svg>
                    </button>
                    <button type="button" data-cmd="unlink" class="btn btn-ghost btn-sm btn-icon" aria-label="شيل الرابط" title="شيل الرابط">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M18 9l3-3M6 15l-3 3M4 4l16 16"/><path d="M10.5 13.5a4 4 0 0 0 5.7 0"/></svg>
                    </button>
                    <button type="button" data-cmd="image" class="btn btn-ghost btn-sm btn-icon" aria-label="صورة" title="صورة">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m4 17 5-5 4 4 3-2 4 4"/></svg>
                    </button>
                    <button type="button" data-cmd="hr" class="btn btn-ghost btn-sm btn-icon" aria-label="فاصل" title="فاصل">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 12h16"/></svg>
                    </button>
                    <button type="button" data-cmd="clean" class="btn btn-ghost btn-sm text-2xs" title="شيل التنسيق">تنظيف</button>

                    <input type="file" data-editor-upload accept="image/*" class="sr-only" aria-hidden="true" tabindex="-1">
                </div>

                {{-- The visible surface. The <textarea> below it is what posts. --}}
                <div data-editor-surface contenteditable="true" role="textbox" aria-multiline="true" aria-label="نص المقال"
                     class="post-editor min-h-[26rem] p-5 text-cream-2 focus:outline-none"></div>

                <textarea name="body" class="sr-only" aria-hidden="true" tabindex="-1">{{ old('body', $post->body) }}</textarea>
            </section>

            <section class="panel p-5" aria-labelledby="seo-fields-h">
                <h2 id="seo-fields-h" class="text-sm font-semibold text-cream">حقول السيو</h2>
                <p class="mt-1 text-2xs leading-[1.8] text-cream-3">اللي بتكتبه هنا هو اللي بيظهر في نتايج البحث وفي المشاركة على واتساب وفيسبوك.</p>

                <div class="mt-4 flex flex-col gap-4">
                    <div>
                        <label for="focus_keyword" class="label">الكلمة المفتاحية</label>
                        <input id="focus_keyword" name="focus_keyword" value="{{ old('focus_keyword', $post->focus_keyword) }}" class="field"
                               placeholder="بطاطس مجمّدة للمطاعم">
                        <p class="hint">الكلمة اللي عايز المقال يترتّب عليها. المساعد على الجنب بيقيس عليها.</p>
                    </div>

                    <div>
                        <label for="meta_title" class="label">عنوان الميتا</label>
                        <input id="meta_title" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="field"
                               placeholder="سيبه فاضي عشان ياخد عنوان المقال">
                    </div>

                    <div>
                        <label for="meta_description" class="label">وصف الميتا</label>
                        <textarea id="meta_description" name="meta_description" rows="3" class="field"
                                  placeholder="السطرين اللي تحت العنوان في جوجل — بين 110 و 160 حرف">{{ old('meta_description', $post->meta_description) }}</textarea>
                    </div>

                    <div>
                        <label for="slug" class="label">الرابط</label>
                        <div class="flex items-center gap-2">
                            <span class="ltr-iso shrink-0 text-2xs text-cream-3" dir="ltr">/blog/</span>
                            <input id="slug" name="slug" data-slug-target value="{{ old('slug', $post->slug) }}" class="field">
                        </div>
                        @error('slug')<p class="error">{{ $message }}</p>@enderror
                        <p class="hint">بيتولّد من العنوان على السيرفر لحد ما تعدّله بإيدك. غيّره بعد النشر وهتحتاج تحويل 301.</p>
                    </div>

                    <div>
                        <label for="canonical_url" class="label">الرابط الأساسي (canonical)</label>
                        <input id="canonical_url" name="canonical_url" type="url" dir="ltr" value="{{ old('canonical_url', $post->canonical_url) }}" class="field text-start"
                               placeholder="سيبه فاضي إلا لو المقال منشور في مكان تاني الأول">
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-navy-2 p-3">
                            <input type="checkbox" name="noindex" value="1" @checked(old('noindex', $post->noindex)) class="mt-0.5 size-4 accent-flame">
                            <span>
                                <span class="block text-sm text-cream">noindex</span>
                                <span class="block text-2xs text-cream-3">امنع جوجل من فهرسة المقال ده.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-navy-2 p-3">
                            <input type="checkbox" name="nofollow" value="1" @checked(old('nofollow', $post->nofollow)) class="mt-0.5 size-4 accent-flame">
                            <span>
                                <span class="block text-sm text-cream">nofollow</span>
                                <span class="block text-2xs text-cream-3">ماتمرّرش وزن لروابط المقال.</span>
                            </span>
                        </label>
                    </div>
                </div>
            </section>
        </div>

        {{-- ─────────────────────────────────────────────── the pinned rail --}}
        <div class="lg:col-span-4">
            <div class="flex flex-col gap-4 lg:sticky lg:top-24">

                <section class="panel p-5" aria-labelledby="pub-h">
                    <h2 id="pub-h" class="text-sm font-semibold text-cream">النشر</h2>

                    <div class="mt-4 flex flex-col gap-4">
                        <div>
                            <label for="status" class="label">الحالة</label>
                            <select id="status" name="status" class="field">
                                @foreach(\App\Models\Post::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', $post->status) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="hint">«مجدول» بينشر لوحده لما التاريخ ييجي — مفيش cron محتاج يشتغل.</p>
                        </div>

                        <div>
                            <label for="published_at" class="label">تاريخ النشر</label>
                            <input id="published_at" name="published_at" type="datetime-local" class="field num"
                                   value="{{ old('published_at', ($post->published_at ?: now())->format('Y-m-d\TH:i')) }}">
                        </div>

                        <div>
                            <label for="category_id" class="label">القسم</label>
                            <select id="category_id" name="category_id" class="field">
                                <option value="">من غير قسم</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id', $post->category_id) == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tags" class="label">الوسوم</label>
                            <input id="tags" name="tags" class="field" list="tag-list"
                                   value="{{ old('tags', $post->exists ? $post->tags->pluck('name')->implode(', ') : '') }}"
                                   placeholder="سنتانا, قلي, مطاعم">
                            <datalist id="tag-list">
                                @foreach($allTags as $t)<option value="{{ $t->name }}"></option>@endforeach
                            </datalist>
                            <p class="hint">افصل بفاصلة. الوسم الجديد بيتعمل لوحده.</p>
                        </div>
                    </div>
                </section>

                <section class="panel p-5" aria-labelledby="cover-h">
                    <h2 id="cover-h" class="text-sm font-semibold text-cream">صورة المقال</h2>

                    <img src="{{ $post->cover_image ?: '' }}" alt="" data-cover-preview
                         class="mt-3 aspect-16/9 w-full rounded-lg border border-navy-2 object-cover {{ $post->cover_image ? '' : 'hidden' }}"
                         id="cover-preview">

                    <div class="mt-3 flex flex-col gap-3">
                        <input type="text" name="cover_image" id="cover_image" dir="ltr" class="field num text-start text-xs"
                               value="{{ old('cover_image', $post->cover_image) }}" placeholder="/img/…" aria-label="مسار الصورة">

                        <label class="btn btn-ghost btn-sm cursor-pointer">
                            ارفع صورة
                            <input type="file" accept="image/*" class="sr-only"
                                   data-cover-upload data-cover-target="#cover_image" data-cover-preview="#cover-preview"
                                   data-upload-url="{{ route('admin.media.store') }}">
                        </label>

                        <div>
                            <label for="cover_alt" class="label">النص البديل (alt)</label>
                            <input id="cover_alt" name="cover_alt" value="{{ old('cover_alt', $post->cover_alt) }}" class="field"
                                   placeholder="وصف اللي في الصورة">
                        </div>

                        <div>
                            <label for="og_image" class="label">صورة المشاركة</label>
                            <input id="og_image" name="og_image" dir="ltr" class="field num text-start text-xs"
                                   value="{{ old('og_image', $post->og_image) }}" placeholder="سيبها فاضية عشان تاخد صورة المقال">
                        </div>
                    </div>
                </section>

                {{-- The live assistant. Every number here comes from the server,
                     so it is the same score that gets stored on save. --}}
                <section class="panel" data-seo-panel
                         data-endpoint="{{ route('admin.posts.seo') }}"
                         data-origin="{{ \App\Support\Seo::origin() }}"
                         aria-labelledby="seo-h">
                    <div class="panel-head">
                        <h2 id="seo-h" class="panel-title">مساعد السيو</h2>
                        <p class="text-2xs text-cream-3"><span class="num" data-seo-words>{{ $seo['words'] }}</span> كلمة</p>
                    </div>

                    <div class="flex items-center gap-4 p-5">
                        <div class="relative shrink-0">
                            <svg width="66" height="66" viewBox="0 0 66 66" aria-hidden="true" class="-rotate-90">
                                <circle cx="33" cy="33" r="26" fill="none" stroke="var(--surface-2)" stroke-width="6"/>
                                <circle cx="33" cy="33" r="26" fill="none" stroke="var(--good)" stroke-width="6" stroke-linecap="round"
                                        data-seo-ring style="stroke-dasharray:163.4;stroke-dashoffset:{{ 163.4 - 163.4 * $seo['score'] / 100 }}"
                                        class="transition-[stroke-dashoffset] duration-500 ease-out-quart"/>
                            </svg>
                            <span class="num absolute inset-0 grid place-items-center text-lg font-semibold text-cream" data-seo-score>{{ $seo['score'] }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-cream" data-seo-verdict>—</p>
                            <p class="mt-1 text-2xs leading-[1.7] text-cream-3">الدرجة بتتحدّث وانت بتكتب.</p>
                        </div>
                    </div>

                    {{-- What the result actually looks like in Google. --}}
                    <div class="border-y border-navy-2 bg-navy/40 p-4">
                        <p class="text-2xs text-cream-3">شكله في جوجل</p>
                        <div class="mt-2">
                            <p class="truncate text-2xs text-cream-3 ltr-iso" dir="ltr" data-snippet-url>—</p>
                            <p class="mt-1 line-clamp-2 text-sm leading-[1.6] text-frost-ink" data-snippet-title>—</p>
                            <p class="mt-1 line-clamp-3 text-2xs leading-[1.7] text-cream-2" data-snippet-desc>—</p>
                        </div>
                    </div>

                    <ul data-seo-checks class="thin-scroll max-h-[26rem] overflow-y-auto px-4 py-1"></ul>
                </section>
            </div>
        </div>
    </form>

    @if($post->exists && auth()->user()->can_('blog.edit'))
        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="mt-4 lg:max-w-xs"
              data-confirm="هيتمسح مقال «{{ $post->title }}». لو كان منشور، الرابط هيبقى 404 — اعمله تحويل الأول.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm w-full">امسح المقال</button>
        </form>
    @endif
@endsection
