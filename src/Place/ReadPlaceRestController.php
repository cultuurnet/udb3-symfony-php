<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\Hydra\PagedCollection;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Place\ReadModel\Lookup\PlaceLookupServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use Symfony\Component\HttpFoundation\Request;

class ReadPlaceRestController
{
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
        $place = $this->service->getEntity($cdbid);

        $response = JsonLdResponse::create()
            ->setContent($place)
            ->setPublic()
            ->setClientTtl(60 * 30)
            ->setTtl(60 * 5);

        $response->headers->set('Vary', 'Origin');

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

        $ids = $this->lookupService->findPlacesByPostalCode($zipCode);

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
