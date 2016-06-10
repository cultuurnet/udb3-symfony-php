<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Place\ReadModel\Lookup\PlaceLookupServiceInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use Symfony\Component\HttpFoundation\Response;

class ReadPlaceRestControllerTest extends \PHPUnit_Framework_TestCase
{
    const EXISTING_ID = 'existingId';
    const NON_EXISTING_ID = 'nonExistingId';
    const REMOVED_ID = 'removedId';

    /**
     * @var ReadPlaceRestController
     */
    private $placeRestController;

    /**
     * @var JsonDocument
     */
    private $jsonDocument;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->jsonDocument = new JsonDocument('id', 'place');

        $entityServiceInterface = $this->getMock(EntityServiceInterface::class);

        $lookupService = $this->getMock(PlaceLookupServiceInterface::class);

        $entityServiceInterface->method('getEntity')
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
         * @var EntityServiceInterface $entityServiceInterface
         * @var PlaceLookupServiceInterface $lookupService
         */
        $this->placeRestController = new ReadPlaceRestController(
            $entityServiceInterface,
            $lookupService
        );
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_json_get_for_an_event()
    {
        $jsonResponse = $this->placeRestController->get(self::EXISTING_ID);

        $this->assertEquals(Response::HTTP_OK, $jsonResponse->getStatusCode());
        $this->assertEquals($this->jsonDocument->getRawBody(), $jsonResponse->getContent());
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_error_NOT_FOUND_for_getting_a_non_existing_event()
    {
        $jsonResponse = $this->placeRestController->get(self::NON_EXISTING_ID);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $jsonResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_error_HTTP_GONE_for_getting_a_removed_event()
    {
        $jsonResponse = $this->placeRestController->get(self::REMOVED_ID);

        $this->assertEquals(Response::HTTP_GONE, $jsonResponse->getStatusCode());
    }
}
