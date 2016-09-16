<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultureFeed_User;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Commands\PreflightCommand;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Symfony\Deserializer\Event\MajorInfoJSONDeserializer;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use CultuurNet\UDB3\Symfony\OfferRestBaseController;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * @var UsedLabelsMemoryServiceInterface
     */
    protected $usedLabelsMemory;

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
     * @param UsedLabelsMemoryServiceInterface $used_labels_memory
     *   The event labeller.
     * @param CultureFeed_User $user
     *   The culturefeed user.
     * @param IriGeneratorInterface $iriGenerator
     * @param MediaManagerInterface $mediaManager
     * @param SecurityInterface $security
     */
    public function __construct(
        EventServiceInterface $event_service,
        EventEditingServiceInterface $eventEditor,
        UsedLabelsMemoryServiceInterface $used_labels_memory,
        CultureFeed_User $user,
        MediaManagerInterface $mediaManager,
        IriGeneratorInterface $iriGenerator,
        SecurityInterface $security
    ) {
        parent::__construct($eventEditor, $mediaManager);
        $this->eventService = $event_service;
        $this->usedLabelsMemory = $used_labels_memory;
        $this->user = $user;
        $this->iriGenerator = $iriGenerator;
        $this->security = $security;

        $this->majorInfoDeserializer = new MajorInfoJSONDeserializer();
    }

    /**
     * Creates a json-ld response.
     *
     * @return BinaryFileResponse
     *   The response.
     */
    public function eventContext()
    {
        $response = new BinaryFileResponse('/udb3/api/1.0/event.jsonld');
        $response->headers->set('Content-Type', 'application/ld+json');
        return $response;
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
     * Modifies the event tile.
     *
     * @param Request $request
     *   The request.
     * @param string $cdbid
     *   The event id.
     * @param string $language
     *   The event language.
     *
     * @return JsonResponse
     *   The response.
     */
    public function title(Request $request, $cdbid, $language)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (!$body_content->title) {
            return new JsonResponse(['error' => "title required"], 400);
        }

        $command_id = $this->editor->translateTitle(
            $cdbid,
            new Language($language),
            $body_content->title
        );

        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Modifies the event description.
     *
     * @param Request $request
     *   The request.
     * @param string $cdbid
     *   The event id.
     * @param string $language
     *   The event language.
     *
     * @return JsonResponse
     *   The response.
     */
    public function description(Request $request, $cdbid, $language)
    {
        // If it's the main language, it should use updateDescription instead of translate.
        if ($language == Event::MAIN_LANGUAGE_CODE) {
            return parent::updateDescription($request, $cdbid, $language);
        }

        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (!isset($body_content->description)) {
            return new JsonResponse(['error' => "description required"], 400);
        }

        $command_id = $this->editor->translateDescription(
            $cdbid,
            new Language($language),
            $body_content->description
        );

        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Adds a label.
     *
     * @param Request $request
     *   The request.
     * @param string $cdbid
     *   The event id.
     *
     * @return JsonResponse
     *   The response.
     */
    public function addLabel(Request $request, $cdbid)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        $label = new Label($body_content->label);
        $command_id = $this->eventEditor->label(
            $cdbid,
            $label
        );

        $user = $this->user;
        $this->usedLabelsMemory->rememberLabelUsed(
            $user->id,
            $label
        );

        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Deletes a label.
     *
     * @param Request $request
     *   The request.
     * @param string $cdbid
     *   The event id.
     * @param string $label
     *   The label.
     *
     * @return JsonResponse
     *   The response.
     */
    public function deleteLabel(Request $request, $cdbid, $label)
    {
        $response = new JsonResponse();

        $command_id = $this->eventEditor->unlabel(
            $cdbid,
            new Label($label)
        );

        $response->setData(['commandId' => $command_id]);

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

        $event_id = $this->editor->createEvent(
            $majorInfo->getTitle(),
            $majorInfo->getType(),
            $majorInfo->getLocation(),
            $majorInfo->getCalendar(),
            $majorInfo->getTheme()
        );

        $response->setData(
            [
                'eventId' => $event_id,
                'url' => $this->iriGenerator->iri($event_id)
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
