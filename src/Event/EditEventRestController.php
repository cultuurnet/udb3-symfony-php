<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\Commands\PreflightCommand;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Symfony\Deserializer\Event\MajorInfoJSONDeserializer;
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
     * @param EventEditingServiceInterface $eventEditor
     *   The event editor.
     * @param IriGeneratorInterface $iriGenerator
     * @param MediaManagerInterface $mediaManager
     * @param SecurityInterface $security
     */
    public function __construct(
        EventEditingServiceInterface $eventEditor,
        MediaManagerInterface $mediaManager,
        IriGeneratorInterface $iriGenerator,
        SecurityInterface $security
    ) {
        parent::__construct($eventEditor, $mediaManager);
        $this->iriGenerator = $iriGenerator;
        $this->security = $security;

        $this->majorInfoDeserializer = new MajorInfoJSONDeserializer();
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
}
