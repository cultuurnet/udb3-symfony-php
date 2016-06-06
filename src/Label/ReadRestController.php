<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        return new JsonResponse($this->entityAsArray($entity));
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
