<?php

namespace CultuurNet\UDB3\Symfony\Dashboard;

use CultuurNet\Hydra\PagedCollection;
use CultuurNet\Hydra\Symfony\PageUrlGenerator;
use CultuurNet\UDB3\Dashboard\DashboardItemLookupServiceInterface;
use CultuurNet\UiTIDProvider\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use ValueObjects\Number\Natural;

class DashboardRestController
{
    /**
     * @var DashboardItemLookupServiceInterface
     */
    private $itemLookupService;

    /**
     * @var \CultureFeed_User
     */
    private $currentUser;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * DashboardRestController constructor.
     * @param DashboardItemLookupServiceInterface $lookupService
     * @param \CultureFeed_User $currentUser
     */
    public function __construct(
        DashboardItemLookupServiceInterface $lookupService,
        \CultureFeed_User $currentUser,
        UrlGenerator $urlGenerator
    ) {
        $this->itemLookupService = $lookupService;
        $this->currentUser = User::fromCultureFeedUser($currentUser);
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function itemsOwnedByCurrentUser(Request $request)
    {
        $pageNumber = intval($request->query->get('page', 1));
        $limit = 50;

        $items = $this->itemLookupService->findByUser(
            $this->currentUser->id,
            Natural::fromNative($limit),
            Natural::fromNative(--$pageNumber * $limit)
        );

        $pageUrlFactory = new PageUrlGenerator(
            $request->query,
            $this->urlGenerator,
            'dashboard-items',
            'page'
        );

        return JsonResponse::create(
            new PagedCollection(
                $pageNumber,
                $limit,
                $items->getItems(),
                $items->getTotalItems()->toNative(),
                $pageUrlFactory
            )
        );
    }
}
