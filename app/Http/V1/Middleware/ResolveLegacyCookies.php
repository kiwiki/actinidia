<?php

namespace App\Http\V1\Middleware;

use Closure;

class ResolveLegacyCookies
{

    /**
     * A list of cookie names to check for legacy values. Cookies in this list
     * will be search for a legacy counterpart. If a legacy cookie is found, it
     * overwrites the original cookie, if present. Cookies in this list will
     * also be checked for existence in every response. If found, a copy of the
     * cookie will be created with a "-legacy" suffix.
     *
     * This is needed for browsers that don't support SameSite=None. To add
     * backward-compatible same-site cookie support for these browsers, cookies
     * must be sent duplicated within the response: one with SameSite=None and
     * one without SameSite. The cookie without SameSite should have the
     * "-legacy" suffix added, so this middleware will know how to handle it.
     *
     * For instance, if "example" is added to this list, and the response
     * contains cookies named "example" and "example-legacy", the
     * "example-legacy" cookie will be deleted, and its value will be assigned
     * to the "example" cookie. Note that the "example" cookie will have its
     * value replaced on the process.
     */
    protected array $cookies = [
        'token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        // Remove duplicate cookies by using their legacy version if available
        foreach ($this->cookies as $cookie) {
            if ($value = $request->cookie("${cookie}-legacy")) {
                $request->cookies->set($cookie, $value);
                $request->cookies->remove("${cookie}-legacy");
            }
        }

        $response = $next($request);

        // Create legacy versions of cookies that require so
        foreach ($response->headers->getCookies() as $cookie) {
            if (in_array($cookie->getName(), $this->cookies)) {
                $legacy = cookie(
                    $cookie->getName() . '-legacy',
                    $cookie->getValue(),
                    $cookie->getMaxAge() / 60,
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly(),
                    $cookie->isRaw(),
                );

                $response->headers->setCookie($legacy);
            }
        }

        return $response;
    }
}
