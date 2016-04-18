<?php

namespace CultuurNet\UDB3\Symfony\Proxy;

use CultuurNet\UDB3\Symfony\Proxy\Filter\AndFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\AcceptFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\MethodFilter;
use CultuurNet\UDB3\Symfony\Proxy\Redirect\RedirectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class CdbXmlProxy
{
    /**
     * @var Url
     */
    private $redirectUrl;

    /**
     * @var AndFilter
     */
    private $cdbXmlFilter;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * CdbXmlProxy constructor.
     * @param StringLiteral $accept
     * @param Url $redirectUrl
     * @param RedirectInterface $redirect
     */
    public function __construct(
        StringLiteral $accept,
        Url $redirectUrl,
        RedirectInterface $redirect
    ) {
        $acceptFilter = new AcceptFilter($accept);
        $methodFilter = new MethodFilter(new StringLiteral(Request::METHOD_GET));

        $this->cdbXmlFilter = new AndFilter(array(
            $acceptFilter,
            $methodFilter
        ));

        $this->redirectUrl = $redirectUrl;

        $this->redirect = $redirect;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function handle(Request $request)
    {
        if ($this->cdbXmlFilter->matches($request)) {
            return $this->redirect->getRedirectResponse($this->redirectUrl);
        } else {
            return null;
        }
    }
}
