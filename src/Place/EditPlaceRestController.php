<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\Commands\PreflightCommand;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Symfony\Deserializer\Place\MajorInfoJSONDeserializer;
use CultuurNet\UDB3\Symfony\OfferRestBaseController;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\StringLiteral\StringLiteral;

/**
 * Class EditPlaceRestController.
 *
 * @package Drupal\culturefeed_udb3\Controller
 */
class EditPlaceRestController extends OfferRestBaseController
{
    /**
     * The place editor.
     *
     * @var PlaceEditingServiceInterface
     */
    protected $editor;

    /**
     * The event relations repository.
     *
     * @var RepositoryInterface
     */
    protected $eventRelationsRepository;

    /**
     * @var SecurityInterface
     */
    protected $security;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var MajorInfoJSONDeserializer
     */
    protected $majorInfoDeserializer;

    /**
     * Constructs a RestController.
     *
     * @param PlaceEditingServiceInterface $placeEditor
     * @param RepositoryInterface          $event_relations_repository
     * @param SecurityInterface            $security
     * @param MediaManagerInterface        $mediaManager
     * @param IriGeneratorInterface        $iriGenerator
     */
    public function __construct(
        PlaceEditingServiceInterface $placeEditor,
        RepositoryInterface $event_relations_repository,
        SecurityInterface $security,
        MediaManagerInterface $mediaManager,
        IriGeneratorInterface $iriGenerator
    ) {
        parent::__construct($placeEditor, $mediaManager);
        $this->eventRelationsRepository = $event_relations_repository;
        $this->security = $security;
        $this->iriGenerator = $iriGenerator;

        $this->majorInfoDeserializer = new MajorInfoJSONDeserializer();
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
     * Create a new place.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPlace(Request $request)
    {
        $majorInfo = $this->majorInfoDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $place_id = $this->editor->createPlace(
            $majorInfo->getTitle(),
            $majorInfo->getType(),
            $majorInfo->getAddress(),
            $majorInfo->getCalendar(),
            $majorInfo->getTheme()
        );

        return new JsonResponse(
            [
                'placeId' => $place_id,
                'url' => $this->iriGenerator->iri($place_id),
            ]
        );
    }

    /**
     * Remove a place.
     *
     * @param string $cdbid
     * @return JsonResponse
     */
    public function deletePlace($cdbid)
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
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateMajorInfo(Request $request, $cdbid)
    {
        $majorInfo = $this->majorInfoDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editor->updateMajorInfo(
            $cdbid,
            $majorInfo->getTitle(),
            $majorInfo->getType(),
            $majorInfo->getAddress(),
            $majorInfo->getCalendar(),
            $majorInfo->getTheme()
        );

        return new JsonResponse(['commandId' => $commandId]);
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
        $command = new PreflightCommand($cdbid, Permission::AANBOD_BEWERKEN());
        $has_permission = $this->security->isAuthorized($command);

        return JsonResponse::create(['hasPermission' => $has_permission]);
    }
}
