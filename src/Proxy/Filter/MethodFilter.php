<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class MethodFilter implements FilterInterface
{
    /**
     * @var StringLiteral
     */
    private $method;

    public function __construct(StringLiteral $method)
    {
        $this->method = $method;
    }

    /**
     * @inheritdoc
     */
    public function matches(Request $request)
    {
        $method = $request->getMethod();
        return ($method === $this->method->toNative());
    }
}
