<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Redirect;

use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Web\Url;

interface RedirectInterface
{
    /**
     * @param Url $url
     * @return Response
     */
    public function getRedirectResponse(Url $url);
}
