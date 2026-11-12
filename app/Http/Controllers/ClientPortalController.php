<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function showLogin($slug)
    {
        $client = Client::where('slug', $slug)->firstOrFail();

        return view('portal.login', compact('client'));
    }

    public function authenticate(Request $request, $slug)
    {
        $client = Client::where('slug', $slug)->firstOrFail();

        Log::info('Client login attempt', [
            'slug' => $slug,
            'login_input' => $request->input('login'),
            'client_email' => $client->admin_email,
            'client_username' => $client->username,
            'client_status' => $client->status,
            'password_length' => strlen($request->input('password', '')),
            'stored_password_hash' => substr($client->admin_password, 0, 10).'...',
        ]);

        if ($client->status !== 'active') {
            return back()->withErrors(['login' => 'This account is suspended or pending.'])->withInput();
        }

        $loginInput = trim($request->input('login', ''));
        $password = $request->input('password', '');

        if (empty($loginInput) || empty($password)) {
            return back()->withErrors(['login' => 'Please enter credentials.'])->withInput();
        }

        $emailMatch = (strtolower($client->admin_email) === strtolower($loginInput));
        $usernameMatch = ($client->username && strtolower($client->username) === strtolower($loginInput));

        Log::info('Login match check', [
            'email_match' => $emailMatch,
            'username_match' => $usernameMatch,
        ]);

        if (! $emailMatch && ! $usernameMatch) {
            return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
        }

        $passwordValid = false;

        try {
            if (Hash::check($password, $client->admin_password)) {
                $passwordValid = true;
                Log::info('Password hash check passed');
            } else {
                Log::info('Password hash check failed');
            }
        } catch (\Exception $e) {
            Log::error('Hash check exception', ['error' => $e->getMessage()]);
        }

        if (! $passwordValid) {
            return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
        }

        $request->session()->put('client_portal_auth', [
            'client_id' => $client->id,
            'slug' => $client->slug,
            'logged_in_at' => now()->toIso8601String(),
        ]);

        $client->last_login_at = now();
        $client->save();

        Log::info('Client login successful', ['client_id' => $client->id]);

        return redirect()->route('client.portal.dashboard', ['slug' => $slug]);
    }

    public function dashboard($slug)
    {
        $auth = session('client_portal_auth');
        if (! $auth || $auth['slug'] !== $slug) {
            return redirect()->route('client.portal.login', ['slug' => $slug]);
        }
        $client = Client::find($auth['client_id']);
        if (! $client || $client->status !== 'active') {
            session()->forget('client_portal_auth');

            return redirect()->route('client.portal.login', ['slug' => $slug]);
        }

        return view('portal.dashboard', compact('client'));
    }

    public function logout(Request $request, $slug)
    {
        $request->session()->forget('client_portal_auth');

        return redirect()->route('client.portal.login', ['slug' => $slug]);
    }

    public function page(Request $request, ClientPortalWorkspaceController $workspace): View
    {
        return $workspace->page($request);
    }

    public function storeAccount(Request $request, ClientPortalWorkspaceController $workspace): RedirectResponse
    {
        return $workspace->storeAccount($request);
    }

    public function storeItem(Request $request, ClientPortalWorkspaceController $workspace): RedirectResponse
    {
        return $workspace->storeItem($request);
    }

    public function storeBill(Request $request, ClientPortalWorkspaceController $workspace): RedirectResponse
    {
        return $workspace->storeBill($request);
    }

    public function storeVoucher(Request $request, ClientPortalWorkspaceController $workspace): RedirectResponse
    {
        return $workspace->storeVoucher($request);
    }

    public function updateSettings(Request $request, ClientPortalWorkspaceController $workspace): RedirectResponse
    {
        return $workspace->updateSettings($request);
    }

    public function updateAiSettings(Request $request, ClientPortalWorkspaceController $workspace): RedirectResponse
    {
        return $workspace->updateAiSettings($request);
    }

    public function chat(Request $request, ClientPortalWorkspaceController $workspace): JsonResponse
    {
        return $workspace->chat($request);
    }
}
