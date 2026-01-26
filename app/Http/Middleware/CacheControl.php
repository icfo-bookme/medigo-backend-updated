<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CacheControl
{
    public function handle(Request $request, Closure $next)
    {
        /** @var SymfonyResponse $response */
        $response = $next($request);


        if (auth()->check() || $this->hasForms($response)) {

            if ( $request->is('*.css') || $request->is('*.png') || $request->is('*.jpg') || $request->is('*.jpeg') || $request->is('*.gif') || $request->is('*.svg')) {
                $response->header("Cache-Control", "private, max-age=86400  ,must-revalidate");
            }


            // Cache private responses for 1 hour with revalidation
            $response->setCache(['private' => true, 'max_age' => 0, 'must_revalidate' => true]);
        } else {
            if ( $request->is('*.css') || $request->is('*.png') || $request->is('*.jpg') || $request->is('*.jpeg') || $request->is('*.gif') || $request->is('*.svg')) {
                $response->header("Cache-Control", "public, max-age=86400  , must-revalidate");
            }


            // Cache public responses for 1 minute with revalidation
            $response->setCache(['public' => true, 'max_age' => 0  , 's_maxage' => 0 , 'must_revalidate' => true]);

            // Set ETag for revalidation
            $etag = md5($response->getContent());
            $response->setEtag($etag);

            // Check if the client's ETag matches the current ETag
            if ($request->getEtags() && in_array($etag, $request->getEtags())) {
                $response->setNotModified();
            }

            // Remove all cookies
            foreach ($response->headers->getCookies() as $cookie) {
                $response->headers->removeCookie($cookie->getName());
            }
        }

        return $response;
    }

    /** @param Response|ResponseFactory $response */
    protected function hasForms($response): bool
    {
        $content = strtolower($response->getContent());

        return Str::of($content)->contains('<input type="hidden" name="_token"');
    }
}
