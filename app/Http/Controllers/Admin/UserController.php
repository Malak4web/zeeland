<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'الإيميل ده مستخدم.',
            'password.confirmed' => 'تأكيد كلمة السر مش مطابق.',
        ]);

        $user = User::create($data + ['is_active' => true]);
        Activity::log('created', "أضاف مستخدم {$user->name} ({$user->roleLabel()})", $user);

        return back()->with('ok', 'المستخدم اتضاف.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // The last active admin can neither demote nor deactivate themselves —
        // that is how a dashboard ends up with nobody who can fix it.
        $isActive = $request->boolean('is_active');
        if ($this->wouldOrphan($user, $data['role'], $isActive)) {
            return back()->withErrors(['role' => 'لازم يفضل مدير واحد نشط على الأقل.']);
        }

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $user->update($data + ['is_active' => $isActive]);
        Activity::log('updated', "عدّل المستخدم {$user->name}", $user);

        return back()->with('ok', 'اتحدّث.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'مينفعش تمسح حسابك انت.']);
        }
        if ($this->wouldOrphan($user, 'sales', false)) {
            return back()->withErrors(['user' => 'ده آخر مدير نشط.']);
        }

        $name = $user->name;
        $user->delete();
        Activity::log('deleted', "مسح المستخدم {$name}", $user);

        return back()->with('ok', 'اتمسح.');
    }

    public function activity(Request $request)
    {
        return view('admin.activity.index', [
            'activities' => \App\Models\Activity::query()
                ->with('user')
                ->when($request->query('user'), fn ($q, $u) => $q->where('user_id', $u))
                ->when($request->query('action'), fn ($q, $a) => $q->where('action', $a))
                ->latest()
                ->paginate(50)->withQueryString(),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function wouldOrphan(User $user, string $newRole, bool $active): bool
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            return false;
        }
        if ($newRole === 'admin' && $active) {
            return false;
        }

        return User::where('role', 'admin')->where('is_active', true)->count() <= 1;
    }
}
