<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultuurNet\UDB3\ApiGuard\ApiKey\Reader\ApiKeyReaderInterface;
use CultuurNet\UDB3\ApiGuard\Consumer\ConsumerReadRepositoryInterface;
use CultuurNet\UDB3\ApiGuard\Consumer\Specification\ConsumerSpecificationInterface;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Event\ValueObjects\LocationId;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarForEventDataValidator;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarJSONParser;
use CultuurNet\UDB3\Symfony\Deserializer\Event\CreateEventJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Event\MajorInfoJSONDeserializer;
use CultuurNet\UDB3\Symfony\OfferRestBaseController;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\StringLiteral\StringLiteral;

class EditEventRestController extends OfferRestBaseController
{
    /**
     * The event editor
     * @var EventEditingServiceInterface
     */
    protected $editor;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var MajorInfoJSONDeserializer
     */
    protected $majorInfoDeserializer;

    /**
     * @var CreateEventJSONDeserializer
     */
    protected $createEventJSONDeserializer;

    /**
     * @var CalendarJSONDeserializer
     */
    protected $calendarDeserializer;

    /**
     * @var ApiKeyReaderInterface
     */
    private $apiKeyReader;

    /**
     * @var ConsumerReadRepositoryInterface
     */
    private $consumerReadRepository;

    /**
     * @var ConsumerSpecificationInterface
     */
    private $shouldApprove;

    /**
     * Constructs a RestController.
     *
     * @param EventEditingServiceInterface $eventEditor
     *   The event editor.
     * @param MediaManagerInterface $mediaManager
     * @param IriGeneratorInterface $iriGenerator
     * @param ApiKeyReaderInterface $apiKeyReader
     * @param ConsumerReadRepositoryInterface $consumerReadRepository
     * @param ConsumerSpecificationInterface $shouldApprove
     */
    public function __construct(
        EventEditingServiceInterface $eventEditor,
        MediaManagerInterface $mediaManager,
        IriGeneratorInterface $iriGenerator,
        ApiKeyReaderInterface $apiKeyReader,
        ConsumerReadRepositoryInterface $consumerReadRepository,
        ConsumerSpecificationInterface $shouldApprove
    ) {
        parent::__construct($eventEditor, $mediaManager);
        $this->iriGenerator = $iriGenerator;

        $this->apiKeyReader = $apiKeyReader;
        $this->consumerReadRepository = $consumerReadRepository;
        $this->shouldApprove = $shouldApprove;

        $this->majorInfoDeserializer = new MajorInfoJSONDeserializer();
        $this->createEventJSONDeserializer = new CreateEventJSONDeserializer();
        $this->calendarDeserializer = new CalendarJSONDeserializer(
            new CalendarJSONParser(),
            new CalendarForEventDataValidator()
        );
    }

    /**
     * Create a new event.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws \CultuurNet\Deserializer\DataValidationException
     */
    public function createEvent(Request $request)
    {
        $response = new JsonResponse();

        $createEvent = $this->createEventJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $apiKey = $this->apiKeyReader->read($request);

        $consumer = null;
        if ($apiKey) {
            $consumer = $this->consumerReadRepository->getConsumer($apiKey);
        }

        $approve = false;
        if ($consumer) {
            $approve = $this->shouldApprove->satisfiedBy($consumer);
        }

        $createMethod = $approve ? 'createApprovedEvent' : 'createEvent';

        $eventId = $this->editor->$createMethod(
            $createEvent->getMainLanguage(),
            $createEvent->getTitle(),
            $createEvent->getType(),
            $createEvent->getLocation(),
            $createEvent->getCalendar(),
            $createEvent->getTheme()
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
     *
     * @param string $cdbid
     * @return JsonResponse
     */
    public function deleteEvent($cdbid)
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
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     *
     * @throws \CultuurNet\Deserializer\DataValidationException
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
     * @param string $cdbid
     * @param string $locationId
     *
     * @return JsonResponse
     */
    public function updateLocation($cdbid, $locationId)
    {
        $commandId = $this->editor->updateLocation(
            $cdbid,
            new LocationId($locationId)
        );

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateAudience(Request $request, $cdbid)
    {
        if (empty($cdbid)) {
            return new JsonResponse(['error' => 'cdbid is required.'], 400);
        }

        $bodyAsArray = json_decode($request->getContent(), true);
        if (!isset($bodyAsArray['audienceType'])) {
            return new JsonResponse(['error' => 'audience type is required.'], 400);
        }

        $audience = new Audience(
            AudienceType::fromNative($bodyAsArray['audienceType'])
        );

        $commandId = $this->editor->updateAudience($cdbid, $audience);

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function copyEvent(Request $request, $cdbid)
    {
        $copyCalendar = $this->calendarDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $copiedEventId = $this->editor->copyEvent($cdbid, $copyCalendar);

        return JsonResponse::create([
            'eventId' => $copiedEventId,
            'url' => $this->iriGenerator->iri($copiedEventId),
        ]);
    }
}
