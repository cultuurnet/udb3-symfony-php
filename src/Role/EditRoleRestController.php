<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Role\Commands\AbstractCommand;
use CultuurNet\UDB3\Role\Commands\UpdateRoleRequestDeserializer;
use CultuurNet\UDB3\Role\Services\RoleEditingServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class EditRoleRestController
{
    /**
     * @var RoleEditingServiceInterface
     */
    private $service;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * EditRoleRestController constructor.
     * @param RoleEditingServiceInterface $service
     * @param CommandBusInterface $commandBus
     */
    public function __construct(RoleEditingServiceInterface $service, CommandBusInterface $commandBus)
    {
        $this->service = $service;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(Request $request)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());


        if (empty($body_content->name)) {
            throw new \InvalidArgumentException('Required fields are missing');
        }

        $roleId = $this->service->create(
            new StringLiteral($body_content->name)
        );

        $response->setData(
            [
                'roleId' => $roleId
            ]
        );

        return $response;
    }

    /**
     * @param Request $request
     * @param $roleId
     * @return JsonResponse
     */
    public function update(Request $request, $roleId)
    {
        $requestDeserializer = new UpdateRoleRequestDeserializer();
        
        $command = $requestDeserializer->deserialize($request, $roleId);

        return $this->commandResponse($command);
    }

    /**
     * Dispatches the command and returns a JsonResponse with its id.
     *
     * @param AbstractCommand $command
     *
     * @return JsonResponse
     */
    private function commandResponse(AbstractCommand $command)
    {
        $commandId = $this->commandBus->dispatch($command);

        return JsonResponse::create(
            ['commandId' => $commandId]
        );
    }
}
