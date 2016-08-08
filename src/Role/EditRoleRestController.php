<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Broadway\CommandHandling\CommandBusInterface;
use Crell\ApiProblem\ApiProblem;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use CultuurNet\UDB3\Role\Commands\AbstractCommand;
use CultuurNet\UDB3\Role\Commands\UpdateRoleRequestDeserializer;
use CultuurNet\UDB3\Role\Services\RoleEditingServiceInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @var ReadServiceInterface
     */
    private $labelEntityService;

    /**
     * EditRoleRestController constructor.
     * @param RoleEditingServiceInterface $service
     * @param CommandBusInterface $commandBus
     * @param UpdateRoleRequestDeserializer $updateRoleRequestDeserializer
     * @param ReadServiceInterface $labelEntityService
     */
    public function __construct(
        RoleEditingServiceInterface $service,
        CommandBusInterface $commandBus,
        UpdateRoleRequestDeserializer $updateRoleRequestDeserializer,
        ReadServiceInterface $labelEntityService
    ) {
        $this->service = $service;
        $this->commandBus = $commandBus;
        $this->updateRoleRequestDeserializer = $updateRoleRequestDeserializer;
        $this->labelEntityService = $labelEntityService;
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
     * @param string $roleId
     * @param string $labelIdentifier
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function addLabel($roleId, $labelIdentifier)
    {
        $roleId = (string) $roleId;
        $labelId = $this->getLabelId($labelIdentifier);

        if (is_null($labelId)) {
            $apiProblem = new ApiProblem('There is no label with identifier: ' . $labelIdentifier);
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);
            return new ApiProblemJsonResponse($apiProblem);
        }

        try {
            $roleId = new UUID($roleId);
        } catch (InvalidNativeArgumentException $e) {
            throw new InvalidArgumentException('Required field roleId is not a valid uuid.');
        }

        $commandId = $this->service->addLabel($roleId, $labelId);

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
    }

    /**
     * @param string $roleId
     * @param string $labelIdentifier
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function removeLabel($roleId, $labelIdentifier)
    {
        $roleId = (string) $roleId;
        $labelId = $this->getLabelId($labelIdentifier);

        if (is_null($labelId)) {
            $apiProblem = new ApiProblem('There is no label with identifier: ' . $labelIdentifier);
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);
            return new ApiProblemJsonResponse($apiProblem);
        }

        try {
            $roleId = new UUID($roleId);
        } catch (InvalidNativeArgumentException $e) {
            throw new InvalidArgumentException('Required field roleId is not a valid uuid.');
        }

        $commandId = $this->service->removeLabel($roleId, $labelId);

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

    /**
     * @param string $labelIdentifier
     * @return UUID|null
     */
    private function getLabelId($labelIdentifier)
    {
        try {
            return new UUID($labelIdentifier);
        } catch (InvalidNativeArgumentException $exception) {
            $entity = $this->labelEntityService->getByName(
                new StringLiteral($labelIdentifier)
            );

            return is_null($entity) ? null : $entity->getUuid();
        }
    }
}
