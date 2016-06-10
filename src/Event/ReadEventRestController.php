<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultuurNet\UDB3\Symfony\ApiProblemJsonResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;

class ReadEventRestController
{
    const HISTORY_ERROR_NOT_FOUND = 'An error occurred while getting the history of the event with id %s!';
    const HISTORY_ERROR_GONE = 'An error occurred while getting the history of the event with id %s which was removed!';
    const GET_ERROR_NOT_FOUND = 'An error occurred while getting the event with id %s!';
    const GET_ERROR_GONE = 'An error occurred while getting the event with id %s which was removed!';

    use ApiProblemJsonResponseTrait;

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
     * @param string $cdbid
     * @return JsonLdResponse
     */
    public function get($cdbid)
    {
        $response = null;

        try {
            $event = $this->service->getEvent($cdbid);

            if ($event) {
                $response = JsonLdResponse::create()
                    ->setContent($event);

                $response->headers->set('Vary', 'Origin');
            } else {
                $response = $this->createApiProblemJsonResponseNotFound(self::GET_ERROR_NOT_FOUND, $cdbid);
            }
        } catch (DocumentGoneException $documentGoneException) {
            $response = $this->createApiProblemJsonResponseGone(self::GET_ERROR_GONE, $cdbid);
        }

        return $response;
    }

    /**
     * @param string $cdbid
     * @return JsonResponse
     */
    public function history($cdbid)
    {
        $response = null;

        try {
            $document = $this->historyRepository->get($cdbid);

            if ($document) {
                $response = JsonResponse::create()
                    ->setContent($document->getRawBody());

                $response->headers->set('Vary', 'Origin');
            } else {
                $response = $this->createApiProblemJsonResponseNotFound(self::HISTORY_ERROR_NOT_FOUND, $cdbid);
            }
        } catch (DocumentGoneException $documentGoneException) {
            $response = $this->createApiProblemJsonResponseGone(self::HISTORY_ERROR_GONE, $cdbid);
        }

        return $response;
    }
}
