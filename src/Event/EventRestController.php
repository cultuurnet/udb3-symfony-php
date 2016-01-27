<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventRestController
{
    /**
     * @var EventServiceInterface
     */
    private $service;

    /**
     * @var DocumentRepositoryInterface
     */
    private $historyRepository;

    /**
     * @param EventServiceInterface $service
     * @param DocumentRepositoryInterface $historyRepository
     */
    public function __construct(
        EventServiceInterface $service,
        DocumentRepositoryInterface $historyRepository
    ) {
        $this->service = $service;
        $this->historyRepository = $historyRepository;
    }

    /**
     * @param $cdbid
     * @return JsonLdResponse
     */
    public function get($cdbid)
    {
        $event = $this->service->getEvent($cdbid);

        $response = JsonLdResponse::create()
            ->setContent($event);

        $response->headers->set('Vary', 'Origin');

        return $response;
    }

    /**
     * @param $cdbid
     * @return JsonResponse
     */
    public function history($cdbid)
    {
        $document = $this->historyRepository->get($cdbid);

        $response = JsonResponse::create()
            ->setContent($document->getRawBody());

        $response->headers->set('Vary', 'Origin');

        return $response;
    }
}
