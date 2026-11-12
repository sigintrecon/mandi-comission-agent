<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = (string) $request->route('slug');
        $auth = $request->session()->get('client_portal_auth');

        if (! is_array($auth) || ($auth['slug'] ?? null) !== $slug || ! isset($auth['client_id'])) {
            return redirect()->route('client.portal.login', ['slug' => $slug]);
        }

        $client = Client::query()
            ->whereKey($auth['client_id'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! $client) {
            $request->session()->forget('client_portal_auth');

            return redirect()->route('client.portal.login', ['slug' => $slug]);
        }

        $request->attributes->set('clientPortalClient', $client);

        return $next($request);
    }
}
