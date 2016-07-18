<?php

namespace CultuurNet\UDB3\Symfony\Role;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Services\RoleReadingServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class ReadRoleRestControllerTest extends \PHPUnit_Framework_TestCase
{
    const EXISTING_ID = 'existingId';
    const NON_EXISTING_ID = 'nonExistingId';
    const REMOVED_ID = 'removedId';

    /**
     * @var ReadRoleRestController
     */
    private $roleRestController;

    /**
     * @var JsonDocument
     */
    private $jsonDocument;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->jsonDocument = new JsonDocument('id', 'role');

        $entityServiceInterface = $this->getMock(EntityServiceInterface::class);

        $roleService = $this->getMock(RoleReadingServiceInterface::class);

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
         * @var RoleReadingServiceInterface $roleService
         */
        $this->roleRestController = new ReadRoleRestController(
            $entityServiceInterface,
            $roleService,
            new \CultureFeed_User(),
            array()
        );
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_json_get_for_a_role()
    {
        $jsonResponse = $this->roleRestController->get(self::EXISTING_ID);

        $this->assertEquals(Response::HTTP_OK, $jsonResponse->getStatusCode());
        $this->assertEquals($this->jsonDocument->getRawBody(), $jsonResponse->getContent());
    }

    /**
     * @test
     */
    public function it_returns_a_http_response_with_error_NOT_FOUND_for_getting_a_non_existing_role()
    {
        $jsonResponse = $this->roleRestController->get(self::NON_EXISTING_ID);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $jsonResponse->getStatusCode());
    }
}
