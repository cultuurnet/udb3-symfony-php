<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Role\Commands\AbstractCommand;
use CultuurNet\UDB3\Role\Commands\UpdateRoleRequestDeserializer;
use CultuurNet\UDB3\Role\Services\RoleEditingServiceInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\Identity\UUID;
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
     * @var UpdateRoleRequestDeserializer
     */
    private $updateRoleRequestDeserializer;

    /**
     * EditRoleRestController constructor.
     * @param RoleEditingServiceInterface $service
     * @param CommandBusInterface $commandBus
     * @param UpdateRoleRequestDeserializer $updateRoleRequestDeserializer
     */
    public function __construct(
        RoleEditingServiceInterface $service,
        CommandBusInterface $commandBus,
        UpdateRoleRequestDeserializer $updateRoleRequestDeserializer
    ) {
        $this->service = $service;
        $this->commandBus = $commandBus;
        $this->updateRoleRequestDeserializer = $updateRoleRequestDeserializer;
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
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $command = $this->updateRoleRequestDeserializer->deserialize($request, $id);

        return $this->commandResponse($command);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        $roleId = (string) $id;

        if (empty($roleId)) {
            throw new InvalidArgumentException('Required field roleId is missing');
        }

        $commandId = $this->service->delete(new UUID($roleId));

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
    }

    /**
     * @param $roleId
     * @param string $permissionKey
     * @return JsonResponse
     */
    public function addPermission($roleId, $permissionKey)
    {
        $roleId = (string) $roleId;

        if (empty($roleId)) {
            throw new InvalidArgumentException('Required field roleId is missing');
        }

        $permissionKey = (string) $permissionKey;

        if (!in_array($permissionKey, array_keys(Permission::getConstants()))) {
            throw new InvalidArgumentException('Field permission is invalid.');
        }

        $commandId = $this->service->addPermission(
            new UUID($roleId),
            Permission::getByName($permissionKey)
        );

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
    }

    /**
     * @param $roleId
     * @param string $permissionKey
     * @return JsonResponse
     */
    public function removePermission($roleId, $permissionKey)
    {
        $roleId = (string) $roleId;

        if (empty($roleId)) {
            throw new InvalidArgumentException('Required field roleId is missing');
        }

        $permissionKey = (string) $permissionKey;

        if (!in_array($permissionKey, array_keys(Permission::getConstants()))) {
            throw new InvalidArgumentException('Field permission is invalid.');
        }

        $commandId = $this->service->removePermission(
            new UUID($roleId),
            Permission::getByName($permissionKey)
        );

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function addUser($roleId, $userId)
    {
        $roleId = (string) $roleId;
        $userId = (string) $userId;

        try {
            $roleId = new UUID($roleId);
        } catch (InvalidNativeArgumentException $e) {
            throw new InvalidArgumentException('Required field roleId is not a valid uuid.');
        }

        if (empty($userId)) {
            throw new InvalidArgumentException('Required field userId is missing');
        }

        $userId = new StringLiteral($userId);

        $commandId = $this->service->addUser($roleId, $userId);

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
    }

    /**
     * @param $roleId
     * @param $userId
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function removeUser($roleId, $userId)
    {
        $roleId = (string) $roleId;
        $userId = (string) $userId;

        try {
            $roleId = new UUID($roleId);
        } catch (InvalidNativeArgumentException $e) {
            throw new InvalidArgumentException('Required field roleId is not a valid uuid.');
        }

        if (empty($userId)) {
            throw new InvalidArgumentException('Required field userId is missing');
        }

        $userId = new StringLiteral($userId);

        $commandId = $this->service->removeUser($roleId, $userId);

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
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
