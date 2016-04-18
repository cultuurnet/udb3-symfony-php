<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class ContentTypeFilter implements FilterInterface
{
    const CONTENT_TYPE = 'Content-Type';
    /**
     * @var StringLiteral
     */
    private $contentType;

    public function __construct(StringLiteral $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @inheritdoc
     */
    public function matches(Request $request)
    {
        $contentType = $request->headers->get(self::CONTENT_TYPE);
        return ($contentType === $this->contentType->toNative());
    }
}
