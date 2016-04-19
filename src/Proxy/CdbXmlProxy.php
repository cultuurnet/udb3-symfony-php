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
    /**
     * CdbXmlProxy constructor.
     * @param StringLiteral $accept
     * @param Domain $domain
     * @param DiactorosFactory $diactorosFactory
     * @param HttpFoundationFactory $httpFoundationFactory
     * @param ClientInterface $client
     */
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

    /**
     * @param StringLiteral $accept
     * @return AndFilter
     */
    private function createFilter(StringLiteral $accept)
    {
        $acceptFilter = new AcceptFilter($accept);
        $methodFilter = new MethodFilter(new StringLiteral('GET'));

        return new AndFilter([$acceptFilter, $methodFilter]);
    }
}
