<?php

namespace CultuurNet\UDB3\Symfony\Event;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;

class EventRestControllerTest extends PHPUnit_Framework_TestCase
{
    const EXISTING_ID = 'existingId';
    const NON_EXISTING_ID = 'nonExistingId';
    const REMOVED_ID = 'removedId';

    /**
     * @var ReadEventRestController
     */
    private $eventRestController;

    /**
     * @var JsonDocument
     */
    private $jsonDocument;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->jsonDocument = new JsonDocument('id', 'history');

        $eventServiceInterface = $this->createMock(EventServiceInterface::class);

        $documentRepositoryInterface = $this->createMock(DocumentRepositoryInterface::class);
        $documentRepositoryInterface->method('get')
            ->willReturnCallback(
                function ($id) {
                    switch ($id) {
                        case self::EXISTING_ID:
                            return $this->jsonDocument;
                        case self::REMOVED_ID:
                            throw new DocumentGoneException();
                        default:
                            return null;
                    }
                }
            );

        $eventServiceInterface->method('getEvent')
            ->willReturnCallback(
                function ($id) {
                    switch ($id) {
                        case self::EXISTING_ID:
                            return $this->jsonDocument->getRawBody();
                        case self::REMOVED_ID:
                            throw new DocumentGoneException();
                        default:
                            return null;
                    }
                }
            );

        /**
         * @var EventServiceInterface $eventServiceInterface
         * @var DocumentRepositoryInterface $documentRepositoryInterface
         */
        $this->eventRestController = new ReadEventRestController(
            $eventServiceInterface,
            $documentRepositoryInterface
        );
    }

    /**
     * @test
     */
    public function returns_a_http_response_with_json_history_for_an_event()
    {
        $jsonResponse = $this->eventRestController->history(self::EXISTING_ID);

        $this->assertEquals(Response::HTTP_OK, $jsonResponse->getStatusCode());
        $this->assertEquals($this->jsonDocument->getRawBody(), $jsonResponse->getContent());
    }

    /**
     * @test
     */
    public function returns_a_http_response_with_error_NOT_FOUND_for_a_non_existing_event()
    {
        $jsonResponse = $this->eventRestController->history(self::NON_EXISTING_ID);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $jsonResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function returns_a_http_response_with_error_HTTP_GONE_for_a_removed_event()
    {
        $jsonResponse = $this->eventRestController->history(self::REMOVED_ID);

        $this->assertEquals(Response::HTTP_GONE, $jsonResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_json_get_for_an_event()
    {
        $jsonResponse = $this->eventRestController->get(self::EXISTING_ID);

        $this->assertEquals(Response::HTTP_OK, $jsonResponse->getStatusCode());
        $this->assertEquals($this->jsonDocument->getRawBody(), $jsonResponse->getContent());
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_error_NOT_FOUND_for_getting_a_non_existing_event()
    {
        $jsonResponse = $this->eventRestController->get(self::NON_EXISTING_ID);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $jsonResponse->getStatusCode());
    }
}
