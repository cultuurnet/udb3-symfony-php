<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class AcceptFilter implements FilterInterface
{
    const ACCEPT = 'Accept';
    /**
     * @var StringLiteral
     */
    private $accept;

    public function __construct(StringLiteral $accept)
    {
        $this->accept = $accept;
    }

    /**
     * @inheritdoc
     */
    public function matches(Request $request)
    {
        $accept = $request->headers->get(self::ACCEPT);
        return ($accept === $this->accept->toNative());
    }
}
