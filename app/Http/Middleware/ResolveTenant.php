<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request);

        if (!$subdomain) {
            return $next($request); // Platform admin — no tenant
        }

        $tenant = Cache::remember("tenant:subdomain:{$subdomain}", 300, fn () =>
            Tenant::where('subdomain', $subdomain)->first()
        );

        if (!$tenant) {
            abort(404, 'Dealership not found.');
        }

        $this->bootTenant($tenant);

        return $next($request);
    }

    private function extractSubdomain(Request $request): ?string
    {
        $host    = $request->getHost();
        $central = trim(env('CENTRAL_DOMAINS', 'platform.com'));

        // Remove the central domain suffix
        if (str_ends_with($host, '.' . $central)) {
            $sub = str_replace('.' . $central, '', $host);
            return $sub ?: null;
        }

        // The deployed Vercel domain is also a central/platform domain.
        // It must not be treated as a tenant subdomain.
        $appHost = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);
        if ($appHost && strcasecmp($host, $appHost) === 0) {
            return null;
        }

        // On localhost just use query/body param ?tenant=xxx for development,
        // and remember it in the session so it survives redirects that don't
        // carry the param (e.g. post-login redirect to '/').
        if (app()->environment('local')) {
            if ($request->input('tenant')) {
                $request->session()->put('dev_tenant_subdomain', $request->input('tenant'));
                return $request->input('tenant');
            }

            if ($request->session()->has('dev_tenant_subdomain')) {
                return $request->session()->get('dev_tenant_subdomain');
            }

            return 'demo';
        }

        // Production requests that are not explicitly mapped to a tenant
        // belong to the central/platform application.
        return null;
    }

    private function bootTenant(Tenant $tenant): void
    {
        app()->instance('tenant', $tenant);
        setPermissionsTeamId($tenant->id);
        config(['app.timezone' => $tenant->getTimezone()]);
        date_default_timezone_set($tenant->getTimezone());
        config(['cache.prefix' => "t{$tenant->id}"]);
        view()->share('currentTenant', $tenant);
    }
}
