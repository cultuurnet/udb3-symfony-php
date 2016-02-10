<?php

namespace CultuurNet\UDB3\Symfony\SavedSearches;

use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReadSavedSearchesController
{
    /**
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var SavedSearchRepositoryInterface
     */
    private $readRepository;

    /**
     * @param \CultureFeed_User $user
     * @param SavedSearchRepositoryInterface $readRepository
     */
    public function __construct(
        \CultureFeed_User $user,
        SavedSearchRepositoryInterface $readRepository
    ) {
        $this->user = $user;
        $this->readRepository = $readRepository;
    }

    /**
     * @return JsonResponse
     */
    public function ownedByCurrentUser()
    {
        return JsonResponse::create(
            $this->readRepository->ownedByCurrentUser()
        );
    }
}
