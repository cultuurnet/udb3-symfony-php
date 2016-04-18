<?php

namespace CultuurNet\UDB3\Symfony\Proxy\DomainReplacer;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;

class DomainReplacer
{
    /**
     * @param Domain $redirectDomain
     * @param Request $request
     * @return Url
     */
    public function createUrl(Domain $redirectDomain, Request $request)
    {
        $requestUrl = Url::fromNative($request->getUri());
        
        $replacedUrl = new Url(
            $requestUrl->getScheme(),
            $requestUrl->getUser(),
            $requestUrl->getPassword(),
            $redirectDomain,
            $requestUrl->getPort(),
            $requestUrl->getPath(),
            $requestUrl->getQueryString(),
            $requestUrl->getFragmentIdentifier()
        );
        
        return $replacedUrl;
    }
}
