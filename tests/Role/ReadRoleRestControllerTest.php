<?php

namespace CultuurNet\UDB3\Symfony\Role;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\ReadModel\Permissions\UserPermissionsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Search\RepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Search\Results;
use CultuurNet\UDB3\Role\Services\RoleReadingServiceInterface;
use CultuurNet\UDB3\Symfony\Assert\JsonEquals;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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
     * @var RoleReadingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var JsonEquals
     */
    private $jsonEquals;

    /**
     * @var \CultureFeed_User
     */
    private $cfUser;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->jsonDocument = new JsonDocument('id', 'role');

        $entityServiceInterface = $this->getMock(EntityServiceInterface::class);

        $this->roleService = $this->getMock(RoleReadingServiceInterface::class);

        $permissionsRepository = $this->getMock(UserPermissionsReadRepositoryInterface::class);

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

        $this->cfUser = new \CultureFeed_User();

        $this->roleRestController = new ReadRoleRestController(
            $entityServiceInterface,
            $this->roleService,
            $this->cfUser,
            array(),
            $this->roleSearchRepository,
            $permissionsRepository
        );

        $this->jsonEquals = new JsonEquals($this);
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
    public function it_responds_with_an_array_of_users_for_a_given_role_id()
    {
        $roleId = new UUID('57791495-93D0-45CE-8D02-D716EC38972A');

        $readmodelJson = file_get_contents(__DIR__ . '/samples/role_users_readmodel.json');
        $expectedResponseJson = file_get_contents(__DIR__ . '/samples/role_users_response.json');

        $readmodelDocument = new JsonDocument(
            $roleId->toNative(),
            $readmodelJson
        );

        $this->roleService->expects($this->once())
            ->method('getUsersByRoleUuid')
            ->with($roleId)
            ->willReturn($readmodelDocument);

        $response = $this->roleRestController->getRoleUsers($roleId->toNative());
        $actualResponseJson = $response->getContent();

        $this->jsonEquals->assert($expectedResponseJson, $actualResponseJson);
    }

    /**
     * @test
     */
    public function it_responds_with_an_array_of_roles_for_a_given_user_id()
    {
        $userId = new StringLiteral('12345');

        $readmodelJson = file_get_contents(__DIR__ . '/samples/role_users_readmodel.json');
        $expectedResponseJson = file_get_contents(__DIR__ . '/samples/role_users_response.json');

        $readmodelDocument = new JsonDocument(
            $userId->toNative(),
            $readmodelJson
        );

        $this->roleService->expects($this->once())
            ->method('getRolesByUserId')
            ->with($userId)
            ->willReturn($readmodelDocument);

        $response = $this->roleRestController->getUserRoles($userId->toNative());
        $actualResponseJson = $response->getContent();

        $this->jsonEquals->assert($expectedResponseJson, $actualResponseJson);
    }

    /**
     * @test
     */
    public function it_responds_with_an_empty_array_if_no_roles_document_is_found_for_a_given_user_id()
    {
        $userId = new StringLiteral('12345');

        $this->roleService->expects($this->once())
            ->method('getRolesByUserId')
            ->with($userId)
            ->willReturn(null);

        $response = $this->roleRestController->getUserRoles($userId->toNative());
        $responseJson = $response->getContent();

        $this->jsonEquals->assert('[]', $responseJson);
    }

    /**
     * @test
     */
    public function it_responds_with_an_array_of_roles_for_the_current_user()
    {
        $userId = new StringLiteral('12345');
        $this->cfUser->id = $userId->toNative();

        $readmodelJson = file_get_contents(__DIR__ . '/samples/user_roles_readmodel.json');
        $expectedResponseJson = file_get_contents(__DIR__ . '/samples/user_roles_response.json');

        $readmodelDocument = new JsonDocument(
            $userId->toNative(),
            $readmodelJson
        );

        $this->roleService->expects($this->once())
            ->method('getRolesByUserId')
            ->with($userId)
            ->willReturn($readmodelDocument);

        $response = $this->roleRestController->getCurrentUserRoles();
        $actualResponseJson = $response->getContent();

        $this->jsonEquals->assert($expectedResponseJson, $actualResponseJson);
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
