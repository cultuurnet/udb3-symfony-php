<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueConstraintException;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Organizer\OrganizerEditingServiceInterface;
use CultuurNet\UDB3\Symfony\Deserializer\Organizer\OrganizerCreationPayloadJSONDeserializer;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class EditOrganizerRestController
{

    /** @var OrganizerEditingServiceInterface */
    private $editingService;

    /** @var IriGeneratorInterface */
    private $iriGenerator;

    /**
     * @var OrganizerCreationPayloadJSONDeserializer
     */
    private $organizerCreationPayloadDeserializer;

    /**
     * EditOrganizerRestController constructor.
     * @param OrganizerEditingServiceInterface $organizerEditingService
     * @param IriGeneratorInterface            $organizerIriGenerator
     */
    public function __construct(
        OrganizerEditingServiceInterface $organizerEditingService,
        IriGeneratorInterface $organizerIriGenerator
    ) {
        $this->editingService = $organizerEditingService;
        $this->iriGenerator = $organizerIriGenerator;

        $this->organizerCreationPayloadDeserializer = new OrganizerCreationPayloadJSONDeserializer();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws DataValidationException
     */
    public function create(Request $request)
    {
        $payload = $this->organizerCreationPayloadDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        try {
            $organizerId = $this->editingService->create(
                $payload->getWebsite(),
                $payload->getTitle(),
                $payload->getAddress(),
                $payload->getContactPoint()
            );
        } catch (UniqueConstraintException $e) {
            $e = new DataValidationException();
            $e->setValidationMessages(
                ['website' => 'Should be unique but is already in use.']
            );
            throw $e;
        }

        return JsonResponse::create(
            [
                'organizerId' => $organizerId,
                'url' => $this->iriGenerator->iri($organizerId),
            ]
        );
    }

    /**
     * @param string $organizerId
     * @param string $labelName
     * @return Response
     */
    public function addLabel($organizerId, $labelName)
    {
        $commandId = $this->editingService->addLabel(
            $organizerId,
            new Label($labelName)
        );

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @param string $organizerId
     * @param string $labelName
     * @return Response
     */
    public function removeLabel($organizerId, $labelName)
    {
        $commandId = $this->editingService->removeLabel(
            $organizerId,
            new Label($labelName)
        );

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @param string $cdbid
     * @return Response
     */
    public function delete($cdbid)
    {
        $cdbid = (string) $cdbid;

        if (empty($cdbid)) {
            throw new InvalidArgumentException('Required field cdbid is missing');
        }

        $commandId = $this->editingService->delete($cdbid);

        return (new JsonResponse())
            ->setData(['commandId' => $commandId]);
    }
}
