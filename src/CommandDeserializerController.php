<?php

namespace CultuurNet\UDB3\Symfony;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\Deserializer\DeserializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String;

/**
 * Creates a command by deserializing the body of a request using the injected
 * deserializer, and dispatches it to the injected command bus.
 */
class CommandDeserializerController
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var DeserializerInterface
     */
    private $deserializer;

    /**
     * @param DeserializerInterface $commandDeserializer
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        DeserializerInterface $commandDeserializer,
        CommandBusInterface $commandBus
    ) {
        $this->deserializer = $commandDeserializer;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request)
    {
        $command = $this->deserializer->deserialize(
            new String($request->getContent())
        );

        $commandId = $this->commandBus->dispatch($command);

        return JsonResponse::create(
            ['commandId' => $commandId]
        );
    }
}
