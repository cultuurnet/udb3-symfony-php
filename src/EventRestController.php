<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Symfony;

use CultureFeed_User;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\UDB3\CalendarDeserializer;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\UsedLabelsMemory\DefaultUsedLabelsMemoryService;
use Drupal;
use Drupal\file\FileUsage\FileUsageInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventRestController extends OfferRestBaseController
{

    const IMAGE_UPLOAD_DIR = 'public://events';

    /**
     * The search service.
     *
     * @var PullParsingSearchService;
     */
    protected $searchService;

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
     * The file usage interface.
     * @var FileUsageInterface
     */
    protected $fileUsage;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var CalendarDeserializer
     */
    protected $calendarDeserializer;

    /**
     * Constructs a RestController.
     *
     * @param EventServiceInterface $event_service
     *   The event service.
     * @param EventEditingServiceInterface $event_editor
     *   The event editor.
     * @param DefaultUsedLabelsMemoryService $used_labels_memory
     *   The event labeller.
     * @param CultureFeed_User $user
     *   The culturefeed user.
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(
        EventServiceInterface $event_service,
        EventEditingServiceInterface $event_editor,
        DefaultUsedLabelsMemoryService $used_labels_memory,
        CultureFeed_User $user,
        FileUsageInterface $fileUsage = null,
        IriGeneratorInterface $iriGenerator
    ) {
        $this->eventService = $event_service;
        $this->editor = $event_editor;
        $this->usedLabelsMemory = $used_labels_memory;
        $this->user = $user;
        $this->fileUsage = $fileUsage;
        $this->iriGenerator = $iriGenerator;
        $this->calendarDeserializer = new CalendarDeserializer();
    }

    /**
     * Creates a json-ld response.
     *
     * @return BinaryFileResponse
     *   The response.
     */
    public function eventContext() {
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
    public function details($cdbid) {

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
    public function title(Request $request, $cdbid, $language) {

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
    public function description(Request $request, $cdbid, $language) {

        // If it's the main language, it should use updateDescription instead of translate.
        if ($language == Event::MAIN_LANGUAGE_CODE) {
            return parent::updateDescription($request, $cdbid, $language);
        }

        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (!$body_content->description) {
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
    public function addLabel(Request $request, $cdbid) {

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
    public function deleteLabel(Request $request, $cdbid, $label) {

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
    public function createEvent(Request $request) {

        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());


        if (empty($body_content->name) || empty($body_content->type) || empty($body_content->location) || empty($body_content->calendarType)) {
            throw new \InvalidArgumentException('Required fields are missing');
        }

        $theme = null;
        if (!empty($body_content->theme) && !empty($body_content->theme->id)) {
            $theme = new Theme($body_content->theme->id, $body_content->theme->label);
        }

        $event_id = $this->editor->createEvent(
            new Title($body_content->name->nl),
            new EventType($body_content->type->id, $body_content->type->label),
            new Location($body_content->location->id,
                $body_content->location->name,
                $body_content->location->address->addressCountry,
                $body_content->location->address->addressLocality,
                $body_content->location->address->postalCode,
                $body_content->location->address->streetAddress
            ),
            $this->calendarDeserializer->deserialize($body_content),
            $theme
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
    public function deleteEvent(Request $request, $cdbid) {

        $response = new JsonResponse();

        if (empty($cdbid)) {
            throw new InvalidArgumentException('Required fields are missing');
        }

        $result = $this->editor->deleteEvent($cdbid);
        $response->setData(['result' => $result]);

        return $response;
    }

    /**
     * Update the major info of an item.
     */
    public function updateMajorInfo(Request $request, $cdbid) {

        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (empty($body_content->name) || empty($body_content->type)) {
            throw new InvalidArgumentException('Required fields are missing');
        }

        $theme = null;
        if (!empty($body_content->theme) && !empty($body_content->theme->id)) {
            $theme = new Theme($body_content->theme->id, $body_content->theme->label);
        }

        $command_id = $this->editor->updateMajorInfo(
            $cdbid,
            new Title($body_content->name->nl),
            new EventType($body_content->type->id, $body_content->type->label),
            new Location(
                $body_content->location->id,
                $body_content->location->name,
                $body_content->location->address->addressCountry,
                $body_content->location->address->addressLocality,
                $body_content->location->address->postalCode,
                $body_content->location->address->streetAddress
            ),
            $this->calendarDeserializer->deserialize($body_content),
            $theme
        );

        $response->setData(['commandId' => $command_id]);

        return $response;

    }

    /**
     * TODO: This authenticator is hard coded to Drupal. Move it to a service.
     * Check if the current user has edit access to the given item.
     *
     * @param string cdbid
     *   Id of item to check.
     */
    public function hasPermission($cdbid) {

        $improvedEntryApiFactory = Drupal::service('culturefeed_udb3.udb2_entry_api_improved_factory');
        $userCredentials = Drupal::service('culturefeed.user_credentials');

        $credentials = new TokenCredentials($userCredentials->getToken(), $userCredentials->getSecret());
        $entryApi = $improvedEntryApiFactory->get()->withTokenCredentials($credentials);

        $response = new JsonResponse();

        $result = $entryApi->checkPermission($this->user->id, $this->user->mbox, array($cdbid));
        $has_permission = FALSE;
        if (!empty($result->event)) {
            $has_permission = (string)$result->event->editable == 'true';
        }

        $response->setData(['hasPermission' => $has_permission]);

        return $response;

    }

    /**
     * Get the image destination.
     */
    public function getImageDestination($id) {
        return self::IMAGE_UPLOAD_DIR . '/' . $id;
    }

    /**
     * Get the detail of an item.
     */
    public function getItem($id) {
        return $this->eventService->getEvent($id);
    }

}

