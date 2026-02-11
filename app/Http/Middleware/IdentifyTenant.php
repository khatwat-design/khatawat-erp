<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->header('X-Store-Domain')
            ?? $request->query('domain');

        if (! $domain) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                $queryString = parse_url($referer, PHP_URL_QUERY);
                if (is_string($queryString)) {
                    parse_str($queryString, $params);
                    if (! empty($params['domain'])) {
                        $domain = (string) $params['domain'];
                    }
                }
            }
        }

        if (! $domain) {
            $host = $request->getHost();
            if ($host && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
                $domain = $host;
            }
        }

        if ($domain) {
            $store = Store::query()
                ->where('subdomain', $domain)
                ->orWhere('domain', $domain)
                ->orWhere('custom_domain', $domain)
                ->first();

            if ($store) {
                app()->instance('currentStore', $store);
            }
        }

        if ($this->isStorefrontRequest($request)) {
            $resolvedStore = app()->bound('currentStore') ? app('currentStore') : null;
            Log::info('IdentifyTenant', [
                'domain' => $domain,
                'referer' => $request->headers->get('referer'),
                'resolved_store_id' => $resolvedStore?->id,
                'resolved_store_subdomain' => $resolvedStore?->subdomain,
            ]);
        }

        if (! app()->bound('currentStore') && $this->isStorefrontRequest($request)) {
            return new JsonResponse(['message' => 'Store not found'], 404);
        }

        return $next($request);
    }

    private function isStorefrontRequest(Request $request): bool
    {
        return str_starts_with($request->path(), 'api/storefront');
    }
}
