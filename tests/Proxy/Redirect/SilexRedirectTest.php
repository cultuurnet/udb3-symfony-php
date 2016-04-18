<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Redirect;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Web\Url;

class SilexRedirectTest extends \PHPUnit_Framework_TestCase
{
    const REDIRECT_URL = 'https://udb-cdbxml.uitdatabank.be/event/uuid';

    /**
     * @var RedirectResponse
     */
    private $redirectResponse;
    
    protected function setUp()
    {
        $url = Url::fromNative(self::REDIRECT_URL);
        $silexRedirect = new SilexRedirect();
        
        $this->redirectResponse = $silexRedirect->getRedirectResponse($url);
    }
    
    /**
     * @test
     */
    public function it_returns_redirect_response_with_correct_url()
    {
        $expectedUrl = self::REDIRECT_URL;
        $actualUrl = $this->redirectResponse->getTargetUrl();
            
        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /**
     * @test
     */
    public function it_return_redirect_response_with_status_code_found()
    {
        $expectedStatusCode = Response::HTTP_FOUND;
        $actualStatusCode = $this->redirectResponse->getStatusCode();
        
        $this->assertEquals($expectedStatusCode, $actualStatusCode);
    }
}
