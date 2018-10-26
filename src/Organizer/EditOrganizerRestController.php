<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueConstraintException;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Organizer\OrganizerEditingServiceInterface;
use CultuurNet\UDB3\Symfony\Deserializer\Address\AddressJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\ContactPoint\ContactPointJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Organizer\OrganizerCreationPayloadJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Organizer\UrlJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\TitleJSONDeserializer;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
                $payload->getMainLanguage(),
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
     * @param $organizerId
     * @param Request $request
     * @return JsonResponse
     * @throws DataValidationException
     */
    public function updateUrl($organizerId, Request $request)
    {
        $websiteJSONDeserializer = new UrlJSONDeserializer();
        $website = $websiteJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        try {
            $commandId = $this->editingService->updateWebsite(
                $organizerId,
                $website
            );
        } catch (UniqueConstraintException $e) {
            $e = new DataValidationException(
                [
                    'url' => 'Should be unique but is already in use.',
                ]
            );
            throw $e;
        }

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @deprecated Use updateName with language parameter instead.
     * @param string $organizerId
     * @param Request $request
     * @return JsonResponse
     */
    public function updateNameDeprecated(
        $organizerId,
        Request $request
    ) {
        return $this->updateName($organizerId, 'nl', $request);
    }

    /**
     * @param string $organizerId
     * @param string $lang
     * @param Request $request
     * @return JsonResponse
     */
    public function updateName(
        $organizerId,
        $lang,
        Request $request
    ) {
        $titleJSONDeserializer = new TitleJSONDeserializer(
            false,
            new StringLiteral('name')
        )
        ;
        $title = $titleJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editingService->updateTitle(
            $organizerId,
            $title,
            empty($lang) ? new Language('nl') : new Language($lang)
        );

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @deprecated Use updateAddress with language parameter instead.
     *
     * @param string $organizerId
     * @param Request $request
     * @return JsonResponse
     *
     * @throws DataValidationException
     */
    public function updateAddressDeprecated(
        string $organizerId,
        Request $request
    ) {
        return $this->updateAddress($organizerId, 'nl', $request);
    }

    /**
     * @param string $organizerId
     * @param string $lang
     * @param Request $request
     * @return JsonResponse
     *
     * @throws DataValidationException
     */
    public function updateAddress(
        string $organizerId,
        string $lang,
        Request $request
    ) {
        $addressJSONDeserializer = new AddressJSONDeserializer();

        $address = $addressJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editingService->updateAddress(
            $organizerId,
            $address,
            new Language($lang)
        );

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @param string $organizerId
     * @param Request $request
     * @return JsonResponse
     */
    public function updateContactPoint($organizerId, Request $request)
    {
        $contactPointJSONDeserializer = new ContactPointJSONDeserializer();

        $contactPoint = $contactPointJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editingService->updateContactPoint(
            $organizerId,
            $contactPoint
        );

        return JsonResponse::create(['commandId' => $commandId]);
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
