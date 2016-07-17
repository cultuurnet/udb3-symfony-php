<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Crell\ApiProblem\ApiProblem;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Role\Services\RoleReadingServiceInterface;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;

class ReadRoleRestController
{
    /**
     * @var EntityServiceInterface
     */
    private $service;

    /**
     * @var RoleReadingServiceInterface
     */
    private $roleService;

    /**
     * ReadRoleRestController constructor.
     * @param EntityServiceInterface $service
     * @param RoleReadingServiceInterface $roleService
     */
    public function __construct(
        EntityServiceInterface $service,
        RoleReadingServiceInterface $roleService
    ) {
        $this->service = $service;
        $this->roleService = $roleService;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function get($id)
    {
        $response = null;

        $role = $this->service->getEntity($id);

        if ($role) {
            $response = JsonResponse::create()
                ->setContent($role);

            $response->headers->set('Vary', 'Origin');
        } else {
            $apiProblem = new ApiProblem('There is no role with identifier: ' . $id);
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);

            return new ApiProblemJsonResponse($apiProblem);
        }

        return $response;
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getRolePermissions($id)
    {
        $response = null;

        $document = $this->roleService->getPermissionsByRoleUuid(new UUID($id));

        if ($document) {
            // Return Permissions, even if it is an empty array.
            $body = $document->getBody();
            $response = JsonResponse::create()
                ->setContent(json_encode($body->permissions));

            $response->headers->set('Vary', 'Origin');
        } else {
            $apiProblem = new ApiProblem('There is no role with identifier: ' . $id);
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);

            return new ApiProblemJsonResponse($apiProblem);
        }

        return $response;
    }
}
