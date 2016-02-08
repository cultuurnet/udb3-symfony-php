<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 08/02/16
 * Time: 15:19
 */

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Organizer\OrganizerEditingServiceInterface;
use CultuurNet\UDB3\Organizer\ReadModel\Lookup\OrganizerLookupServiceInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;

class OrganizerControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityServiceInterface
     */
    private $service;

    /**
     * @var OrganizerController
     */
    private $organizerController;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->service = $this->getMock(EntityServiceInterface::class);
        $lookupService = $this->getMock(OrganizerLookupServiceInterface::class);
        $editingService = $this->getMock(OrganizerEditingServiceInterface::class);
        $iriGenerator = $this->getMock(IriGeneratorInterface::class);

        $this->organizerController = new OrganizerController(
            $this->service,
            $lookupService,
            $editingService,
            $iriGenerator
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
