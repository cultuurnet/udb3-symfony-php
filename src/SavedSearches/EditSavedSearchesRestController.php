<?php

namespace CultuurNet\UDB3\Symfony\SavedSearches;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\SavedSearches\Command\SavedSearchCommand;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearchJSONDeserializer;
use CultuurNet\UDB3\SavedSearches\Command\UnsubscribeFromSavedSearch;
use CultuurNet\UDB3\ValueObject\SapiVersion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\StringLiteral\StringLiteral;

class EditSavedSearchesRestController
{
    /**
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param \CultureFeed_User $user
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        \CultureFeed_User $user,
        CommandBusInterface $commandBus
    ) {
        $this->user = $user;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param string $sapiVersion
     * @return JsonResponse
     */
    public function save(Request $request, string $sapiVersion)
    {
        $commandDeserializer = new SubscribeToSavedSearchJSONDeserializer(
            SapiVersion::fromNative($sapiVersion),
            new StringLiteral($this->user->id)
        );

        $command = $commandDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        return $this->commandResponse($command);
    }

    /**
     * @param string $sapiVersion
     * @param string $id
     *
     * @return JsonResponse
     */
    public function delete(string $sapiVersion, string $id)
    {
        $command = new UnsubscribeFromSavedSearch(
            SapiVersion::fromNative($sapiVersion),
            new StringLiteral($this->user->id),
            new StringLiteral($id)
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
