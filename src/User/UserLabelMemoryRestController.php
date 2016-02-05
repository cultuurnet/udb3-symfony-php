<?php

namespace CultuurNet\UDB3\Symfony\User;

use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserLabelMemoryRestController
{
    /**
     * @var UsedLabelsMemoryServiceInterface
     */
    private $memoryService;

    /**
     * @param UsedLabelsMemoryServiceInterface $memoryService
     * @param \CultureFeed_User $user
     */
    public function __construct(
        UsedLabelsMemoryServiceInterface $memoryService,
        \CultureFeed_User $user
    ) {
        $this->memoryService = $memoryService;
        $this->user = $user;
    }

    /**
     * @return JsonResponse
     */
    public function all()
    {
        return new JsonResponse(
            $this->memoryService->getMemory($this->user->id)
        );
    }
}
