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

    public function create(Request $request): JsonResponse
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

    public function updateUrl(string $organizerId, Request $request): Response
    {
        $websiteJSONDeserializer = new UrlJSONDeserializer();
        $website = $websiteJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        try {
            $this->editingService->updateWebsite(
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

        return new Response();
    }

    /**
     * @deprecated Use updateName with language parameter instead.
     */
    public function updateNameDeprecated(
        string $organizerId,
        Request $request
    ): Response {
        return $this->updateName($organizerId, 'nl', $request);
    }

    public function updateName(
        string $organizerId,
        string $lang,
        Request $request
    ): Response {
        $titleJSONDeserializer = new TitleJSONDeserializer(
            false,
            new StringLiteral('name')
        )
        ;
        $title = $titleJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $this->editingService->updateTitle(
            $organizerId,
            $title,
            empty($lang) ? new Language('nl') : new Language($lang)
        );

        return new Response();
    }

    /**
     * @deprecated Use updateAddress with language parameter instead.
     */
    public function updateAddressDeprecated(
        string $organizerId,
        Request $request
    ): Response {
        return $this->updateAddress($organizerId, 'nl', $request);
    }

    public function updateAddress(
        string $organizerId,
        string $lang,
        Request $request
    ): Response {
        $addressJSONDeserializer = new AddressJSONDeserializer();

        $address = $addressJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $this->editingService->updateAddress(
            $organizerId,
            $address,
            new Language($lang)
        );

        return new Response();
    }

    public function updateContactPoint(string $organizerId, Request $request): Response
    {
        $contactPointJSONDeserializer = new ContactPointJSONDeserializer();

        $contactPoint = $contactPointJSONDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $this->editingService->updateContactPoint(
            $organizerId,
            $contactPoint
        );

        return new Response();
    }

    public function addLabel(string $organizerId, string $labelName): Response
    {
        $this->editingService->addLabel(
            $organizerId,
            new Label($labelName)
        );

        return new Response();
    }

    public function removeLabel($organizerId, $labelName): Response
    {
        $this->editingService->removeLabel(
            $organizerId,
            new Label($labelName)
        );

        return new Response();
    }

    public function delete($cdbid): Response
    {
        $cdbid = (string) $cdbid;

        if (empty($cdbid)) {
            throw new InvalidArgumentException('Required field cdbid is missing');
        }

        $this->editingService->delete($cdbid);

        return new Response();
    }
}
