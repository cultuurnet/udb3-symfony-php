<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Symfony\ApiProblemJsonResponseTrait;
use CultuurNet\UDB3\Symfony\JsonLdResponse;

class ReadPlaceRestController
{
    const GET_ERROR_NOT_FOUND = 'An error occurred while getting the event with id %s!';
    const GET_ERROR_GONE = 'An error occurred while getting the event with id %s which was removed!';

    use ApiProblemJsonResponseTrait;

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
     * @return JsonLdResponse
     */
    public function get($cdbid)
    {
        $response = null;

        $place = $this->service->getEntity($cdbid);

        if ($place) {
            $response = JsonLdResponse::create()
                ->setContent($place);

            $response->headers->set('Vary', 'Origin');
        } else {
            $response = $this->createApiProblemJsonResponseNotFound(self::GET_ERROR_NOT_FOUND, $cdbid);
        }

        return $response;
    }
}
