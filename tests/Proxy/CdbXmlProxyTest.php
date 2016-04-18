<?php

namespace CultuurNet\UDB3\Symfony\Proxy;

use CultuurNet\UDB3\Symfony\Proxy\Filter\AcceptFilter;
use CultuurNet\UDB3\Symfony\Proxy\Redirect\RedirectInterface;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Hostname;

class CdbXmlProxyTest extends \PHPUnit_Framework_TestCase
{
    const APPLICATION_XML = 'application/xml';
    const REDIRECT_DOMAIN = 'www.bing.be';

    /**
     * @var RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var CdbXmlProxy
     */
    private $cdbXmlProxy;

    protected function setUp()
    {
        $this->redirect = $this->getMock(RedirectInterface::class);

        /** @var Hostname $redirectDomain */
        $redirectDomain = Hostname::fromNative(self::REDIRECT_DOMAIN);
        
        $this->cdbXmlProxy = new CdbXmlProxy(
            new StringLiteral(self::APPLICATION_XML),
            $redirectDomain,
            $this->redirect
        );
    }

    /**
     * @test
     */
    public function it_calls_getRedirectResponse_when_filters_match()
    {
        $request = $this->createRequest(
            Request::METHOD_GET,
            self::APPLICATION_XML
        );

        $this->redirect->expects($this->once())
            ->method('getRedirectResponse');

        $this->cdbXmlProxy->handle($request);
    }

    /**
     * @test
     */
    public function it_returns_null_when_method_does_not_match()
    {
        $request = $this->createRequest(
            Request::METHOD_POST,
            self::APPLICATION_XML
        );

        $actualResponse = $this->cdbXmlProxy->handle($request);

        $this->assertEquals(null, $actualResponse);
    }

    /**
     * @test
     */
    public function it_does_not_call_getRedirectResponse_when_method_does_not_match()
    {
        $request = $this->createRequest(
            Request::METHOD_POST,
            self::APPLICATION_XML
        );
        
        $this->redirect->expects($this->never())
            ->method('getRedirectResponse');

        $this->cdbXmlProxy->handle($request);
    }

    /**
     * @test
     */
    public function it_returns_null_when_accept_does_not_match()
    {
        $request = $this->createRequest(
            Request::METHOD_GET,
            'application/json'
        );

        $actualResponse = $this->cdbXmlProxy->handle($request);

        $this->assertEquals(null, $actualResponse);
    }

    /**
     * @test
     */
    public function it_does_not_call_getRedirectResponse_when_accept_does_not_match()
    {
        $request = $this->createRequest(
            Request::METHOD_GET,
            'application/json'
        );

        $this->redirect->expects($this->never())
            ->method('getRedirectResponse');

        $this->cdbXmlProxy->handle($request);
    }

    /**
     * @param string $method
     * @param string $accept
     * @return Request
     */
    private function createRequest($method, $accept)
    {
        $request = Request::create('http://www.google.be');
        $request->setMethod($method);
        $request->headers->set(AcceptFilter::ACCEPT, $accept);

        return $request;
    }
}
