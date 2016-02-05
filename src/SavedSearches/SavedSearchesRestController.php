<?php

namespace CultuurNet\UDB3\Symfony\SavedSearches;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\SavedSearches\Command\SavedSearchCommand;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearchJSONDeserializer;
use CultuurNet\UDB3\SavedSearches\Command\UnsubscribeFromSavedSearch;
use CultuurNet\UDB3\SavedSearches\ReadModel\SavedSearchRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String;

class SavedSearchesRestController
{
    /**
     * @var SavedSearchRepositoryInterface
     */
    private $readRepository;

    /**
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param SavedSearchRepositoryInterface $readRepository
     * @param \CultureFeed_User $user
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        SavedSearchRepositoryInterface $readRepository,
        \CultureFeed_User $user,
        CommandBusInterface $commandBus
    ) {
        $this->readRepository = $readRepository;
        $this->user = $user;
        $this->commandBus = $commandBus;
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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        $commandDeserializer = new SubscribeToSavedSearchJSONDeserializer(
            new String($this->user->id)
        );

        $command = $commandDeserializer->deserialize(
            new String($request->getContent())
        );

        return $this->commandResponse($command);
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function delete($id)
    {
        $command = new UnsubscribeFromSavedSearch(
            new String($this->user->id),
            new String($id)
        );

        return $this->commandResponse($command);
    }

    /**
     * Dispatches the command and returns a JsonResponse with its id.
     *
     * @param SavedSearchCommand $command
     *
     * @return JsonResponse
     */
    private function commandResponse(SavedSearchCommand $command)
    {
        $commandId = $this->commandBus->dispatch($command);

        return JsonResponse::create(
            ['commandId' => $commandId]
        );
    }
}
