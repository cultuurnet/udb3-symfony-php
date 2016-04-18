<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class MethodFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        $this->request = new Request();
        $this->request->setMethod(Request::METHOD_GET);
    }

    /**
     * @test
     */
    public function it_does_match_the_same_http_method()
    {
        $methodFilter = new MethodFilter(new StringLiteral(Request::METHOD_GET));

        $this->assertTrue($methodFilter->matches($this->request));
    }

    /**
     * @test
     */
    public function it_does_not_match_for_a_different_http_method()
    {
        $methodFilter = new MethodFilter(new StringLiteral(Request::METHOD_POST));

        $this->assertFalse($methodFilter->matches($this->request));
    }

    /**
     * Default behavior of getMethod is to return Request::METHOD_GET
     * @test
     */
    public function it_does_match_with_get_method_for_empty_request()
    {
        $request = new Request();
        $methodFilter = new MethodFilter(new StringLiteral(Request::METHOD_GET));

        $this->assertTrue($methodFilter->matches($request));
    }
}
