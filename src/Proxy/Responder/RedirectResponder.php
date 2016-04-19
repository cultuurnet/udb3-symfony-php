<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Responder;

use Symfony\Component\HttpFoundation\RedirectResponse;
use ValueObjects\Web\Url;

class RedirectResponder implements ResponderInterface
{
    /**
     * @return RedirectResponse
     * @inheritdoc
     */
    public function getResponse(Url $url)
    {
        // Typecast is needed to force use of full string url.
        return new RedirectResponse((string)$url);
    }
}
