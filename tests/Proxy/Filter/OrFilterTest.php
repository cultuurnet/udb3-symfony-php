<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use GuzzleHttp\Psr7\Request;
use ValueObjects\StringLiteral\StringLiteral;

class OrFilterTest extends \PHPUnit_Framework_TestCase
{
    const APPLICATION_XML = 'application/xml';

    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        $this->request = new Request(
            'POST',
            'http://www.foo.bar',
            [AcceptFilter::ACCEPT => self::APPLICATION_XML]
        );
    }

    /**
     * @test
     */
    public function it_does_match_when_one_filter_matches()
    {
        $orFilter = new OrFilter(array(
            new MethodFilter(new StringLiteral('OPTIONS')),
            new MethodFilter(new StringLiteral('POST'))
        ));

        $this->assertTrue($orFilter->matches($this->request));
    }

    /**
     * @test
     */
    public function it_does_not_match_when_none_of_the_filters_match()
    {
        $orFilter = new OrFilter(array(
            new MethodFilter(new StringLiteral('OPTIONS')),
            new MethodFilter(new StringLiteral('PUT'))
        ));

        $this->assertFalse($orFilter->matches($this->request));
    }
}
