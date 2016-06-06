<?php

namespace CultuurNet\UDB3\Symfony\Label;

use Crell\ApiProblem\ApiProblem;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;

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

    public function __construct(ReadServiceInterface $readService)
    {
        $this->readService = $readService;
    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function getByUuid($uuid)
    {
        $entity = $this->readService->getByUuid(new UUID($uuid));

        if ($entity) {
            return new JsonResponse($this->entityAsArray($entity));
        } else {
            $apiProblem = new ApiProblem('No label found with uuid: ' . $uuid);
            $apiProblem->setStatus(Response::HTTP_NOT_FOUND);

            return new ApiProblemJsonResponse($apiProblem);
        }
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function entityAsArray(Entity $entity)
    {
        // TODO: Implement serializable interface on entity?
        return [
            self::ID => $entity->getUuid()->toNative(),
            self::NAME => $entity->getName()->toNative(),
            self::VISIBILITY => $entity->getVisibility()->toNative(),
            self::PRIVACY => $entity->getPrivacy()->toNative()
        ];
    }
}
