<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Symfony\Component\HttpFoundation\Request;

interface FilterInterface
{
    /**
     * Check if the request matches a certain pattern.
     *
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request);
}
