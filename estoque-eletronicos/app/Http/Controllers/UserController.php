<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::orderBy('name')->paginate(15)]);
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => 'operador', 'active' => true])]);
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));

        return redirect()->route('users.index')->with('success', 'Usuário cadastrado.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if ($user->is($request->user())) {
            $data['role'] = 'admin';
            $data['active'] = true;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Você não pode excluir o seu próprio usuário.');
        }

        $user->delete();

        return back()->with('success', 'Usuário excluído.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $request->merge(['active' => $request->boolean('active')]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'operador'])],
            'active' => ['boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ]);
    }
}
