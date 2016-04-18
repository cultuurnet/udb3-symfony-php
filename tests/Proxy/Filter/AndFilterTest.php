<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class AndFilterTest extends \PHPUnit_Framework_TestCase
{
    const APPLICATION_XML = 'application/xml';

    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        $this->request = new Request();
        $this->request->headers->set(
            ContentTypeFilter::CONTENT_TYPE, self::APPLICATION_XML
        );
        $this->request->setMethod(Request::METHOD_POST);
    }

    /**
     * @test
     */
    public function it_does_match_when_all_filters_match()
    {
        $andFilter = new AndFilter(array(
            new ContentTypeFilter(new StringLiteral(self::APPLICATION_XML)),
            new MethodFilter(new StringLiteral(Request::METHOD_POST))
        ));

        $this->assertTrue($andFilter->matches($this->request));
    }

    /**
     * @test
     */
    public function it_does_not_match_when_at_least_one_filter_does_not_match()
    {
        $andFilter = new AndFilter(array(
            new ContentTypeFilter(new StringLiteral(self::APPLICATION_XML)),
            new MethodFilter(new StringLiteral(Request::METHOD_PUT))
        ));

        $this->assertFalse($andFilter->matches($this->request));
    }
}
