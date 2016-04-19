<?php

namespace CultuurNet\UDB3\Symfony\Proxy;

use CultuurNet\UDB3\Symfony\Proxy\Filter\AcceptFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\AndFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\MethodFilter;
use CultuurNet\UDB3\Symfony\Proxy\RequestTransformer\DomainReplacer;
use GuzzleHttp\ClientInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Domain;

class CdbXmlProxy extends Proxy
{
    public function __construct(
        StringLiteral $accept,
        Domain $domain,
        DiactorosFactory $diactorosFactory,
        HttpFoundationFactory $httpFoundationFactory,
        ClientInterface $client
    ) {
        $cdbXmlFilter = $this->createFilter($accept);

        $requestTransformer = new DomainReplacer($domain);

        parent::__construct(
            $cdbXmlFilter,
            $requestTransformer,
            $diactorosFactory,
            $httpFoundationFactory,
            $client
        );
    }

    private function createFilter(StringLiteral $accept)
    {
        $acceptFilter = new AcceptFilter($accept);
        $methodFilter = new MethodFilter(new StringLiteral('GET'));

        return new AndFilter([$acceptFilter, $methodFilter]);
    }
}
