<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Responder;

use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Web\Url;

interface ResponderInterface
{
    /**
     * @param Url $url
     * @return Response
     */
    public function getResponse(Url $url);
}
