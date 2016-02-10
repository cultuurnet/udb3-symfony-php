<?php

namespace CultuurNet\UDB3\Symfony\Event;

use Crell\ApiProblem\ApiProblem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;

class ReadEventRestController
{
    const HISTORY_ERROR_NOT_FOUND = 'An error occurred while getting the history of the event with id %s!';
    const HISTORY_ERROR_GONE = 'An error occurred while getting the history of the event with id %s which was removed!';

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
        $event = $this->service->getEvent($cdbid);

        $response = JsonLdResponse::create()
            ->setContent($event);

        $response->headers->set('Vary', 'Origin');

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
                $response = $this->createApiProblemJsonResponseNotFound($cdbid);
            }
        } catch (DocumentGoneException $documentGoneException) {
            $response = $this->createApiProblemJsonResponseGone($cdbid);
        }

        return $response;
    }

    /**
     * @param string $cdbid
     * @return ApiProblemJsonResponse
     */
    private function createApiProblemJsonResponseNotFound($cdbid)
    {
        return $this->createApiProblemJsonResponse(
            self::HISTORY_ERROR_NOT_FOUND,
            $cdbid,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param string $cdbid
     * @return ApiProblemJsonResponse
     */
    private function createApiProblemJsonResponseGone($cdbid)
    {
        return $this->createApiProblemJsonResponse(
            self::HISTORY_ERROR_GONE,
            $cdbid,
            Response::HTTP_GONE
        );
    }

    /**
     * @param string $message
     * @param string $cdbid
     * @param int $statusCode
     * @return ApiProblemJsonResponse
     */
    private function createApiProblemJsonResponse($message, $cdbid, $statusCode)
    {
        $apiProblem = new ApiProblem(
            sprintf(
                $message,
                $cdbid
            )
        );
        $apiProblem->setStatus($statusCode);

        return new ApiProblemJsonResponse($apiProblem);
    }
}
