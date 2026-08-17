<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Redirect as RedirectModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.redirects.index', [
            'redirects' => RedirectModel::query()
                ->when($request->query('q'), fn ($q, $t) => $q->where('from_path', 'like', "%{$t}%")
                    ->orWhere('to_path', 'like', "%{$t}%"))
                ->orderByDesc('hits')->orderByDesc('id')
                ->paginate(30)->withQueryString(),
            'totalHits' => (int) RedirectModel::sum('hits'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $redirect = RedirectModel::create($data);
        Activity::log('created', "أضاف تحويل {$redirect->from_path} ← {$redirect->to_path}", $redirect);

        return back()->with('ok', 'التحويل اشتغل.');
    }

    public function update(Request $request, RedirectModel $redirect)
    {
        $redirect->update($this->validated($request, $redirect));

        return back()->with('ok', 'اتحدّث.');
    }

    public function destroy(RedirectModel $redirect)
    {
        $from = $redirect->from_path;
        $redirect->delete();
        Activity::log('deleted', "مسح التحويل {$from}", $redirect);

        return back()->with('ok', 'اتمسح.');
    }

    private function validated(Request $request, ?RedirectModel $redirect = null): array
    {
        $request->merge([
            'from_path' => RedirectModel::normalise((string) $request->input('from_path')),
        ]);

        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:300', Rule::unique('redirects', 'from_path')->ignore($redirect)],
            'to_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', Rule::in([301, 302, 307, 410])],
        ], [
            'from_path.required' => 'الرابط القديم مطلوب.',
            'from_path.unique' => 'فيه تحويل موجود للرابط ده.',
            'to_path.required' => 'الرابط الجديد مطلوب.',
        ]);

        // A redirect that points at itself is an infinite loop, not a redirect.
        if (RedirectModel::normalise($data['to_path']) === $data['from_path']) {
            abort(422, 'الرابط بيحوّل على نفسه.');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
