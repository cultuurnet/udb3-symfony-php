<?php

namespace CultuurNet\UDB3\Symfony;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\Deserializer\DeserializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\StringLiteral\StringLiteral;

trait CommandDeserializerControllerTrait
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus = null;

    /**
     * @param CommandBusInterface $commandBus
     */
    private function setCommandBus(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param DeserializerInterface $deserializer
     * @return Response
     */
    private function handleRequestWithDeserializer(
        Request $request,
        DeserializerInterface $deserializer
    ) {
        $command = $deserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $this->commandBus->dispatch($command);

        return new Response();
    }
}
