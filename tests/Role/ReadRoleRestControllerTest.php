<?php

namespace CultuurNet\UDB3\Symfony\Role;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\ReadModel\Search\RepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Search\Results;
use CultuurNet\UDB3\Role\Services\RoleReadingServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;

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
     * @var RoleReadingServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $roleService;

    /**
     * @var JsonDocument
     */
    private $jsonDocument;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $roleSearchRepository;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->jsonDocument = new JsonDocument('id', 'role');

        $entityServiceInterface = $this->getMock(EntityServiceInterface::class);

        $this->roleService = $this->getMock(RoleReadingServiceInterface::class);

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

        $this->roleSearchRepository = $this->getMock(RepositoryInterface::class);

        /**
         * @var EntityServiceInterface $entityServiceInterface
         * @var RoleReadingServiceInterface $roleService
         */
        $this->roleRestController = new ReadRoleRestController(
            $entityServiceInterface,
            $this->roleService,
            new \CultureFeed_User(),
            array(),
            $this->roleSearchRepository
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

    /**
     * @test
     */
    public function it_returns_labels()
    {
        $roleId = new UUID();

        $this->roleService
            ->expects($this->once())
            ->method('getLabelsByRoleUuid')
            ->with($roleId)
            ->willReturn(
                new JsonDocument(
                    $roleId,
                    json_encode([])
                )
            );

        $response = $this->roleRestController->getRoleLabels($roleId->toNative());

        $this->assertEquals($response->getContent(), '[]');
    }

    /**
     * @test
     */
    public function it_can_search()
    {
        $request = new Request();
        $results = new Results('10', array(), 0);
        $expectedResults = json_encode((object) array(
            'itemsPerPage' => "10",
            'member' => array(),
            'totalItems' => 0,
        ));

        $this->roleSearchRepository
            ->expects($this->once())
            ->method('search')
            ->willReturn($results);

        $actualResult = $this->roleRestController->search($request);
        $this->assertEquals($expectedResults, $actualResult->getContent());
    }
}
