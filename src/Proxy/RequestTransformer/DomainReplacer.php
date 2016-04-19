<?php

namespace CultuurNet\UDB3\Symfony\Proxy\DomainReplacer;

use CultuurNet\UDB3\Symfony\Proxy\RequestTransformer\RequestTransformerInterface;
use Psr\Http\Message\RequestInterface;
use ValueObjects\Web\Domain;

class DomainReplacer implements RequestTransformerInterface
{
    /**
     * @var Domain
     */
    private $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @inheritdoc
     */
    public function transform(RequestInterface $request)
    {
        return $request->withUri(
            $request->getUri()->withHost($this->domain->toNative())
        );
    }
}
