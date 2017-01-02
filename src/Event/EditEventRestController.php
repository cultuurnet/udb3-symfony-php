<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultureFeed_User;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Offer\Commands\PreflightCommand;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Symfony\Deserializer\Event\MajorInfoJSONDeserializer;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use CultuurNet\UDB3\Symfony\OfferRestBaseController;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class EditEventRestController extends OfferRestBaseController
{
    /**
     * The event editor
     * @var EventEditingServiceInterface
     */
    protected $editor;

    /**
     * The event service.
     *
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * The culturefeed user.
     *
     * @var Culturefeed_User
     */
    protected $user;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var SecurityInterface
     */
    protected $security;

    /**
     * @var MajorInfoJSONDeserializer
     */
    protected $majorInfoDeserializer;

    /**
     * Constructs a RestController.
     *
     * @param EventServiceInterface $event_service
     *   The event service.
     * @param EventEditingServiceInterface $eventEditor
     *   The event editor.
     * @param CultureFeed_User $user
     *   The culturefeed user.
     * @param IriGeneratorInterface $iriGenerator
     * @param MediaManagerInterface $mediaManager
     * @param SecurityInterface $security
     */
    public function __construct(
        EventServiceInterface $event_service,
        EventEditingServiceInterface $eventEditor,
        CultureFeed_User $user,
        MediaManagerInterface $mediaManager,
        IriGeneratorInterface $iriGenerator,
        SecurityInterface $security
    ) {
        parent::__construct($eventEditor, $mediaManager);
        $this->eventService = $event_service;
        $this->user = $user;
        $this->iriGenerator = $iriGenerator;
        $this->security = $security;

        $this->majorInfoDeserializer = new MajorInfoJSONDeserializer();
    }

    /**
     * Returns an event.
     *
     * @param string $cdbid
     *   The event id.
     *
     * @return JsonLdResponse
     *   The response.
     */
    public function details($cdbid)
    {
        $event = $this->getItem($cdbid);

        $response = JsonResponse::create()
            ->setContent($event)
            ->setPublic()
            ->setClientTtl(60 * 30)
            ->setTtl(60 * 5);

        return $response;
    }

    /**
     * Create a new event.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createEvent(Request $request)
    {
        $response = new JsonResponse();

        $majorInfo = $this->majorInfoDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $eventId = $this->editor->createEvent(
            $majorInfo->getTitle(),
            $majorInfo->getType(),
            $majorInfo->getLocation(),
            $majorInfo->getCalendar(),
            $majorInfo->getTheme()
        );

        $response->setData(
            [
                'eventId' => $eventId,
                'url' => $this->iriGenerator->iri($eventId)
            ]
        );

        return $response;
    }

    /**
     * Remove an event.
     */
    public function deleteEvent(Request $request, $cdbid)
    {
        $response = new JsonResponse();

        if (empty($cdbid)) {
            throw new InvalidArgumentException('Required fields are missing');
        }

        $command_id = $this->editor->deleteEvent($cdbid);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the major info of an item.
     */
    public function updateMajorInfo(Request $request, $cdbid)
    {
        $majorInfo = $this->majorInfoDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $command_id = $this->editor->updateMajorInfo(
            $cdbid,
            $majorInfo->getTitle(),
            $majorInfo->getType(),
            $majorInfo->getLocation(),
            $majorInfo->getCalendar(),
            $majorInfo->getTheme()
        );

        return JsonResponse::create(['commandId' => $command_id]);
    }

    /**
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateAudience(Request $request, $cdbid)
    {
        $bodyAsArray = json_decode($request->getContent(), true);
        $audience = new Audience(
            AudienceType::fromNative($bodyAsArray['audienceType'])
        );

        $commandId = $this->editor->updateAudience($cdbid, $audience);

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * Check if the current user has edit access to the given item.
     *
     * @param string $cdbid
     *   Id of item to check.
     * @return JsonResponse
     */
    public function hasPermission($cdbid)
    {
        $command = new PreflightCommand($cdbid, Permission::AANBOD_BEWERKEN());
        $has_permission = $this->security->isAuthorized($command);
        return JsonResponse::create(['hasPermission' => $has_permission]);
    }

    /**
     * Get the detail of an item.
     */
    public function getItem($id)
    {
        return $this->eventService->getEvent($id);
    }
}
