<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\Hydra\PagedCollection;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Place\ReadModel\Lookup\PlaceLookupServiceInterface;
use CultuurNet\UDB3\Symfony\ApiProblemJsonResponseTrait;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @var PlaceLookupServiceInterface
     */
    private $lookupService;

    /**
     * @param EntityServiceInterface $service
     * @param PlaceLookupServiceInterface $lookupService
     */
    public function __construct(
        EntityServiceInterface $service,
        PlaceLookupServiceInterface $lookupService
    ) {
        $this->service = $service;
        $this->lookupService = $lookupService;
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

    /**
     * @param Request $request
     * @return JsonLdResponse
     */
    public function getByPostalCode(Request $request)
    {
        // @todo Add & process pagination parameters
        // @todo Validate zipcode
        $zipCode = $request->query->get('zipcode');
        $country = $request->query->get('country');

        $ids = $this->lookupService->findPlacesByPostalCode($zipCode, $country);

        $members = [];
        if (!empty($ids)) {
            $members = array_map(
                function ($id) {
                    return json_decode($this->service->getEntity($id));
                },
                $ids
            );
        }

        $pagedCollection = new PagedCollection(
            1,
            1000,
            $members,
            count($members)
        );

        return new JsonLdResponse($pagedCollection);
    }
}
