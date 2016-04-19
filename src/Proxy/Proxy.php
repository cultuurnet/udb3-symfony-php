<?php

namespace CultuurNet\UDB3\Symfony\Proxy;

use CultuurNet\UDB3\Symfony\Proxy\Filter\FilterInterface;
use CultuurNet\UDB3\Symfony\Proxy\RequestTransformer\RequestTransformerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Proxy
{
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var RequestTransformerInterface
     */
    private $requestTransformer;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Proxy constructor.
     * @param FilterInterface $filter
     * @param RequestTransformerInterface $requestTransformer
     * @param DiactorosFactory $diactorosFactory
     * @param HttpFoundationFactory $httpFoundationFactory
     * @param ClientInterface $client
     */
    public function __construct(
        FilterInterface $filter,
        RequestTransformerInterface $requestTransformer,
        DiactorosFactory $diactorosFactory,
        HttpFoundationFactory $httpFoundationFactory,
        ClientInterface $client
    ) {
        $this->filter = $filter;

        $this->diactorosFactory = $diactorosFactory;

        $this->requestTransformer = $requestTransformer;

        $this->client = $client;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function handle(Request $request)
    {
        $response = null;

        $psr7Request = $this->diactorosFactory->createRequest($request);

        if ($this->filter->matches($psr7Request)) {
            $psr7Request = $this->requestTransformer->transform($psr7Request);

            $psr7Response = $this->client->send($psr7Request);

            $response = $this->httpFoundationFactory->createResponse($psr7Response);
        }

        return $response;
    }
}