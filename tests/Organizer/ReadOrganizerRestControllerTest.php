<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Organizer\ReadModel\Lookup\OrganizerLookupServiceInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class ReadOrganizerRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $service;

    /**
     * @var ReadOrganizerRestController
     */
    private $organizerController;

    /**
     * @var OrganizerLookupServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lookupService;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->service = $this->getMock(EntityServiceInterface::class);
        $this->lookupService = $this->getMock(OrganizerLookupServiceInterface::class);

        $this->organizerController = new ReadOrganizerRestController(
            $this->service,
            $this->lookupService
        );
    }

    /**
     * @test
     */
    public function it_gets_an_organizer_by_uuid()
    {
        $this->service->expects($this->once())
            ->method('getEntity')
            ->with('e63e5188-96e5-40d9-885f-f356ea19d256');

        $this->organizerController->get('e63e5188-96e5-40d9-885f-f356ea19d256');
    }
}
