<?php

namespace CultuurNet\UDB3\Symfony\User;

use CultuurNet\Hydra\PagedCollection;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Web\EmailAddress;

class SearchUserController
{
    public function __construct(
        \ICultureFeed $cultureFeed
    ) {
        $this->cultureFeed = $cultureFeed;
    }

    /**
     * @param Request $request
     * @return JsonLdResponse
     */
    public function search(Request $request)
    {
        $start = (int) $request->query->get('start', 0);
        $limit = (int) $request->query->get('start', 30);

        $searchQuery = new \CultureFeed_SearchUsersQuery();
        $searchQuery->start = $start;
        $searchQuery->max = $limit;

        if ($request->query->get('email', false)) {
            $email = new EmailAddress($request->query->get('email'));
            $searchQuery->mbox = $email->toNative();
            $searchQuery->mboxIncludePrivate = true;
        }

        /** @var \CultureFeed_ResultSet $results */
        $results = $this->cultureFeed->searchUsers($searchQuery);

        /** @var \CultureFeed_SearchUser $user */
        $users = $results->objects;
        $total = $results->total;

        $pageNumber = (int) ceil($start / $limit);

        $pagedCollection = new PagedCollection(
            $pageNumber,
            $limit,
            $users,
            $total
        );

        return JsonLdResponse::create()
            ->setData($pagedCollection)
            ->setPublic()
            ->setClientTtl(60 * 1)
            ->setTtl(60 * 5);
    }
}
