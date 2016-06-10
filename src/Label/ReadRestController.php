<?php

namespace CultuurNet\UDB3\Symfony\Label;

use Crell\ApiProblem\ApiProblem;
use CultuurNet\Hydra\PagedCollection;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use CultuurNet\UDB3\Symfony\Label\Helper\RequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;

class ReadRestController
{
    const ID = 'id';
    const NAME = 'name';
    const VISIBILITY = 'visibility';
    const PRIVACY = 'privacy';

    /**
     * @var ReadServiceInterface
     */
    private $readService;

    /**
     * @var RequestHelper
     */
    private $requestHelper;

    public function __construct(
        ReadServiceInterface $readService,
        RequestHelper $requestHelper
    ) {
        $this->readService = $readService;
        $this->requestHelper = $requestHelper;
    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function getByUuid($uuid)
    {
        $entity = $this->readService->getByUuid(new UUID($uuid));

        if ($entity) {
            return new JsonResponse($entity);
        } else {
            $apiProblem = new ApiProblem('No label found with uuid: ' . $uuid);
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);

            return new ApiProblemJsonResponse($apiProblem);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $query = $this->requestHelper->getQuery($request);

        $totalEntities = $this->readService->searchTotalLabels($query);

        if ($totalEntities) {
            $entities = $this->readService->search($query);

            $pagedCollection = $this->createPagedCollection(
                $query,
                $entities !== null ? $entities : [],
                $totalEntities
            );
            return new JsonResponse($pagedCollection);
        } else {
            $apiProblem = new ApiProblem('No label found for search query.');
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);

            return new ApiProblemJsonResponse($apiProblem);
        }
    }

    /**
     * @param Query $query
     * @param Entity[] $entities
     * @param Natural $totalEntities
     * @return PagedCollection
     */
    private function createPagedCollection(
        Query $query,
        array $entities,
        Natural $totalEntities
    ) {
        $pageNumber = 0;
        $limit = 0;

        if ($query->getOffset() && $query->getLimit()) {
            $pageNumber = (int)($query->getOffset()->toNative() /
                $query->getLimit()->toNative());
        }

        if ($query->getLimit()) {
            $limit = $query->getLimit()->toNative();
        }

        return new PagedCollection(
            $pageNumber,
            $limit,
            $entities,
            $totalEntities->toNative()
        );
    }
}
