<?php

namespace CultuurNet\UDB3\Symfony\Proxy\DomainReplacer;

use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Web\Hostname;
use ValueObjects\Web\Url;

class DomainReplacerTest extends \PHPUnit_Framework_TestCase
{
    const HTTPS = 'https';
    const BING_PATH = '/path/subPath';
    const BING_QUERY = '?param=value';
    const BING = self::HTTPS . '://' . 'www.bing.be' . self::BING_PATH . self::BING_QUERY;

    const GOOGLE = 'www.google.be';

    /**
     * @var Url
     */
    private $replacedUrl;

    protected function setUp()
    {
        /** @var Hostname $redirectDomain */
        $redirectDomain = Hostname::fromNative(self::GOOGLE);

        $request = Request::create(self::BING);

        $domainReplacer = new DomainReplacer();

        $this->replacedUrl = $domainReplacer->createUrl(
            $redirectDomain,
            $request
        );
    }

    /**
     * @test
     */
    public function it_does_replace_the_domain_of_an_url()
    {
        $this->assertEquals(self::GOOGLE, $this->replacedUrl->getDomain());
    }

    /**
     * @test
     */
    public function it_does_keep_the_path_of_an_url()
    {
        $this->assertEquals(self::BING_PATH, $this->replacedUrl->getPath());
    }

    /**
     * @test
     */
    public function it_does_keep_the_query_of_an_url()
    {
        $this->assertEquals(self::BING_QUERY, $this->replacedUrl->getQueryString());
    }

    /**
     * @test
     */
    public function it_does_keep_the_scheme_of_an_url()
    {
        $this->assertEquals(self::HTTPS, $this->replacedUrl->getScheme());
    }
}
