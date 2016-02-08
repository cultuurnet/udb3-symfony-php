<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;

class OrganizerController
{
    /**
     * @var EntityServiceInterface
     */
    private $service;

    /**
     * @param EntityServiceInterface $service
     */
    public function __construct(
        EntityServiceInterface $service
    ) {
        $this->service = $service;
    }

    /**
     * @param $cdbid
     * @return mixed
     */
    public function get($cdbid)
    {
        $organizer = $this->service->getEntity($cdbid);

        $response = JsonLdResponse::create()
            ->setContent($organizer)
            ->setPublic()
            ->setClientTtl(60 * 30)
            ->setTtl(60 * 5);

        $response->headers->set('Vary', 'Origin');

        return $response;
    }
}
