<?php

namespace CultuurNet\UDB3\Symfony\Proxy;

use CultuurNet\UDB3\Symfony\Proxy\Filter\AndFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\FilterInterface;
use CultuurNet\UDB3\Symfony\Proxy\Filter\MethodFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\PathFilter;
use CultuurNet\UDB3\Symfony\Proxy\RequestTransformer\CombinedReplacer;
use CultuurNet\UDB3\Symfony\Proxy\RequestTransformer\DomainReplacer;
use CultuurNet\UDB3\Symfony\Proxy\RequestTransformer\PortReplacer;
use GuzzleHttp\ClientInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Domain;
use ValueObjects\Web\PortNumber;

class FilterPathProxy extends Proxy
{
    /**
     * CdbXmlProxy constructor.
     * @param FilterPathRegex $path
     * @param Domain $domain
     * @param PortNumber $port
     * @param DiactorosFactory $diactorosFactory
     * @param HttpFoundationFactory $httpFoundationFactory
     * @param ClientInterface $client
     */
    public function __construct(
        FilterPathRegex $path,
        Domain $domain,
        PortNumber $port,
        DiactorosFactory $diactorosFactory,
        HttpFoundationFactory $httpFoundationFactory,
        ClientInterface $client
    ) {
        parent::__construct(
            $this->createFilter($path),
            $this->createTransformer($domain, $port),
            $diactorosFactory,
            $httpFoundationFactory,
            $client
        );
    }

    /**
     * @param FilterPathRegex $path
     * @return FilterInterface
     */
    private function createFilter(FilterPathRegex $path)
    {
        $pathFilter = new PathFilter($path);
        $methodFilter = new MethodFilter(new StringLiteral('GET'));

        return new AndFilter([$pathFilter, $methodFilter]);
    }

    /**
     * @param Domain $domain
     * @param PortNumber $port
     * @return CombinedReplacer
     */
    private function createTransformer(
        Domain $domain,
        PortNumber $port
    ) {
        $domainReplacer = new DomainReplacer($domain);
        
        $portReplacer = new PortReplacer($port);
        
        return new CombinedReplacer([$domainReplacer, $portReplacer]);
    }
}
