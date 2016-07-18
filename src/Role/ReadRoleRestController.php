<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Crell\ApiProblem\ApiProblem;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Role\ReadModel\Search\DBALRepository;
use CultuurNet\UDB3\Role\Services\RoleReadingServiceInterface;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;
use CultuurNet\UDB3\Role\ValueObjects\Permission;

class ReadRoleRestController
{
    /**
     * @var DBALRepository
     */
    private $roleSearchRepository;

    /**
     * @var EntityServiceInterface
     */
    private $service;

    /**
     * @var RoleReadingServiceInterface
     */
    private $roleService;

    /**
     * @var \CultureFeed_User
     */
    private $currentUser;

    /**
     * ReadRoleRestController constructor.
     * @param EntityServiceInterface $service
     * @param RoleReadingServiceInterface $roleService
     * @param \CultureFeed_User $currentUser
     * @param $authorizationList
     * @param DBALRepository $roleSearchRepository
     */
    public function __construct(
        EntityServiceInterface $service,
        RoleReadingServiceInterface $roleService,
        \CultureFeed_User $currentUser,
        $authorizationList,
        DBALRepository $roleSearchRepository
    ) {
        $this->service = $service;
        $this->roleService = $roleService;
        $this->currentUser = $currentUser;
        $this->authorizationList = $authorizationList;
        $this->roleSearchRepository = $roleSearchRepository;
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

    /**
     * @return JsonResponse
     */
    public function getUserPermissions()
    {
        $list = [];

        if (in_array(
            $this->currentUser->id,
            $this->authorizationList['allow_all']
        )) {
            $list = $this->createPermissionsList(Permission::getConstants());
        }

        return (new JsonResponse())
            ->setData($list)
            ->setPrivate();
    }

    private function createPermissionsList($permissions)
    {
        $list = [];

        foreach ($permissions as $key => $name) {
            $item = new \StdClass();
            $item->key = $key;
            $item->name = $name;
            $list[] = $item;
        }

        return $list;
    }

    /**
     * @return JsonResponse
     */
    public function getPermissions()
    {
        $list = $this->createPermissionsList(Permission::getConstants());

        return (new JsonResponse())
            ->setData($list)
            ->setPrivate();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        $name = $request->query->get('name') ?: '';
        $itemsPerPage = $request->query->get('limit') ?: 10;
        $start = $request->query->get('start') ?: 0;

        $result = $this->roleSearchRepository->search($name, $itemsPerPage, $start);

        $data = (object) array(
            'itemsPerPage' => $result->getItemsPerPage(),
            'member' => $result->getMember(),
            'totalItems' => $result->getTotalItems(),
        );

        return (new JsonResponse())
            ->setData($data)
            ->setPrivate();
    }
}
