<?php

namespace CultuurNet\UDB3\Symfony\Proxy;

use CultuurNet\UDB3\Symfony\Proxy\DomainReplacer\DomainReplacer;
use CultuurNet\UDB3\Symfony\Proxy\Filter\AndFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\AcceptFilter;
use CultuurNet\UDB3\Symfony\Proxy\Filter\MethodFilter;
use CultuurNet\UDB3\Symfony\Proxy\Redirect\RedirectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Domain;

class CdbXmlProxy
{
    /**
     * @var Domain
     */
    private $redirectDomain;

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
     * @param Domain $redirectDomain
     * @param RedirectInterface $redirect
     */
    public function __construct(
        StringLiteral $accept,
        Domain $redirectDomain,
        RedirectInterface $redirect
    ) {
        $acceptFilter = new AcceptFilter($accept);
        $methodFilter = new MethodFilter(new StringLiteral(Request::METHOD_GET));

        $this->cdbXmlFilter = new AndFilter(array(
            $acceptFilter,
            $methodFilter
        ));

        $this->redirectDomain = $redirectDomain;

        $this->redirect = $redirect;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function handle(Request $request)
    {
        if ($this->cdbXmlFilter->matches($request)) {
            $domainReplacer = new DomainReplacer();
            $replacedUrl = $domainReplacer->createUrl(
                $this->redirectDomain,
                $request
            );

            return $this->redirect->getRedirectResponse($replacedUrl);
        } else {
            return null;
        }
    }
}
