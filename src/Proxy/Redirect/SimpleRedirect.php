<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Redirect;

use Symfony\Component\HttpFoundation\RedirectResponse;
use ValueObjects\Web\Url;

class SimpleRedirect implements RedirectInterface
{
    /**
     * @return RedirectResponse
     * @inheritdoc
     */
    public function getRedirectResponse(Url $url)
    {
        // Typecast is needed to force use of full string url.
        return new RedirectResponse((string)$url);
    }
}
