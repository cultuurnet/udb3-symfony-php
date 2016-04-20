<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Symfony\Place;

use CultureFeed_User;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\SecurityInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use CultuurNet\UDB3\Symfony\OfferRestBaseController;
use CultuurNet\UDB3\Symfony\type;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use Drupal\culturefeed_udb3\EventRelationsRepository;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\Exception;
use CultuurNet\UDB3\CalendarDeserializer;
use ValueObjects\String\String;

/**
 * Class EditPlaceRestController.
 *
 * @package Drupal\culturefeed_udb3\Controller
 */
class EditPlaceRestController extends OfferRestBaseController
{
    /**
     * The entity service.
     *
     * @var EntityServiceInterface
     */
    protected $entityService;

    /**
     * The place editor.
     *
     * @var PlaceEditingServiceInterface
     */
    protected $editor;

    /**
     * The culturefeed user.
     *
     * @var Culturefeed_User
     */
    protected $user;

    /**
     * The event relations repository.
     *
     * @var EventRelationsRepository
     */
    protected $eventRelationsRepository;

    /**
     * @var SecurityInterface
     */
    protected $security;

    /**
     * Constructs a RestController.
     *
     * @param EntityServiceInterface       $entity_service
     *   The entity service.
     * @param PlaceEditingServiceInterface $placeEditor
     * @param RepositoryInterface          $event_relations_repository,
     * @param CultureFeed_User             $user
     *   The culturefeed user.
     * @param SecurityInterface            $security
     * @param FileUsageInterface           $fileUsage
     * @param MediaManagerInterface        $mediaManager
     */
    public function __construct(
        EntityServiceInterface $entity_service,
        PlaceEditingServiceInterface $placeEditor,
        RepositoryInterface $event_relations_repository,
        CultureFeed_User $user,
        SecurityInterface $security,
        MediaManagerInterface $mediaManager
    ) {
        parent::__construct($placeEditor, $mediaManager);
        $this->entityService = $entity_service;
        $this->eventRelationsRepository = $event_relations_repository;
        $this->user = $user;
        $this->security = $security;
        $this->calendarDeserializer = new CalendarDeserializer();
    }

    /**
     * Creates a json-ld response.
     *
     * @return BinaryFileResponse
     *   The response.
     */
    public function placeContext()
    {
        $response = new BinaryFileResponse('/udb3/api/1.0/place.jsonld');
        $response->headers->set('Content-Type', 'application/ld+json');
        return $response;
    }

    /**
     * Returns a place.
     *
     * @param string $cdbid
     *   The place id.
     *
     * @return JsonLdResponse
     *   The response.
     */
    public function details($cdbid)
    {
        $place = $this->getItem($cdbid);

        $response = JsonResponse::create()
            ->setContent($place)
            ->setPublic()
            ->setClientTtl(60 * 30)
            ->setTtl(60 * 5);

        return $response;
    }

    /**
     * Create a new place.
     */
    public function createPlace(Request $request)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (empty($body_content->name) || empty($body_content->type)) {
            throw new InvalidArgumentException('Required fields are missing');
        }

        $theme = null;
        if (!empty($body_content->theme) && !empty($body_content->theme->id)) {
            $theme = new Theme($body_content->theme->id, $body_content->theme->label);
        }

        $address = !empty($body_content->location->address) ? $body_content->location->address : $body_content->address;

        $place_id = $this->editor->createPlace(
            new Title($body_content->name->nl),
            new EventType($body_content->type->id, $body_content->type->label),
            new Address(
                $address->streetAddress,
                $address->postalCode,
                $address->addressLocality,
                $address->addressCountry
            ),
            $this->calendarDeserializer->deserialize($body_content),
            $theme
        );

        $response->setData(
            [
                'placeId' => $place_id,
                /*'url' => $this->getUrlGenerator()->generateFromRoute(
                    'culturefeed_udb3.place',
                    ['cdbid' => $place_id],
                    ['absolute' => TRUE]
                ),*/
            ]
        );

        return $response;
    }

    /**
     * Remove a place.
     */
    public function deletePlace(Request $request, $cdbid)
    {
        $response = new JsonResponse();

        if (empty($cdbid)) {
            throw new InvalidArgumentException('Required fields are missing');
        }

        $command_id = $this->editor->deletePlace($cdbid);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the major info of an item.
     */
    public function updateMajorInfo(Request $request, $cdbid)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        if (empty($body_content->name) || empty($body_content->type)) {
            throw new \InvalidArgumentException('Required fields are missing');
        }

        $theme = null;
        if (!empty($body_content->theme) && !empty($body_content->theme->id)) {
            $theme = new Theme($body_content->theme->id, $body_content->theme->label);
        }

        $address = !empty($body_content->location->address) ? $body_content->location->address : $body_content->address;

        $command_id = $this->editor->updateMajorInfo(
            $cdbid,
            new Title($body_content->name->nl),
            new EventType($body_content->type->id, $body_content->type->label),
            new Address(
                $address->streetAddress,
                $address->postalCode,
                $address->addressLocality,
                $address->addressCountry
            ),
            $this->calendarDeserializer->deserialize($body_content),
            $theme
        );

        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Update the facilities.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateFacilities(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());

        $facilities = array();
        foreach ($body_content->facilities as $facility) {
            $facilities[] = new Facility($facility->id, $facility->label);
        }

        $response = new JsonResponse();

        $command_id = $this->editor->updateFacilities($cdbid, $facilities);
        $response->setData(['commandId' => $command_id]);

        return $response;
    }

    /**
     * Get the detail of an item.
     */
    public function getItem($id)
    {
        return $this->entityService->getEntity($id);
    }

    /**
     * Get the events for a given place.
     *
     * @param string $cdbid
     *
     * @return JsonResponse
     */
    public function getEvents($cdbid)
    {
        $response = new JsonResponse();

        // Load all event relations from the database.
        $events = $this->eventRelationsRepository->getEventsLocatedAtPlace($cdbid);
        if (!empty($events)) {
            $data = ['events' => []];

            foreach ($events as $eventId) {
                $data['events'][] = [
                    '@id' => $eventId,
                ];
            }

            $response->setData($data);
        }

        return $response;
    }

    /**
     * Check if the current user has edit access to the given item.
     *
     * @param string $cdbid
     *   Id of item to check.
     *
     * @return JsonResponse
     */
    public function hasPermission($cdbid)
    {
        $has_permission = $this->security->allowsUpdates(new String($cdbid));

        return JsonResponse::create(['hasPermission' => $has_permission]);
    }
}
