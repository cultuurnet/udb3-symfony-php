<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\Hydra\PagedCollection;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Organizer\ReadModel\Lookup\OrganizerLookupServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;

class ReadOrganizerRestController
{
    /**
     * @var EntityServiceInterface
     */
    private $service;

    /**
     * @var OrganizerLookupServiceInterface
     */
    private $lookupService;

    /**
     * OrganizerController constructor.
     * @param EntityServiceInterface           $service
     * @param OrganizerLookupServiceInterface  $organizerLookupService
     */
    public function __construct(
        EntityServiceInterface $service,
        OrganizerLookupServiceInterface $organizerLookupService
    ) {
        $this->service = $service;
        $this->lookupService = $organizerLookupService;
    }

    /**
     * Get an organizer by its cdbid.
     * @param string $cdbid
     * @return JsonLdResponse $response
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

    /**
     * @param string $term
     * @return JsonLdResponse
     */
    public function findByPartOfTitle($term)
    {
        // @todo Add & process pagination parameters

        $ids = $this->lookupService->findOrganizersByPartOfTitle($term);

        $members = [];
        if (!empty($ids)) {
            $organizerService = $this->service;

            $members = array_map(
                function ($id) use ($organizerService) {
                    return json_decode($organizerService->getEntity($id));
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

        return (new JsonLdResponse($pagedCollection));
    }
}
