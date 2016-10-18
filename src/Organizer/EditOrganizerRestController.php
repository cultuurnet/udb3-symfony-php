<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Organizer\OrganizerEditingServiceInterface;
use CultuurNet\UDB3\Title;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;

class EditOrganizerRestController
{

    /** @var OrganizerEditingServiceInterface */
    private $editingService;

    /** @var IriGeneratorInterface */
    private $iriGenerator;

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
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        try {
            if (empty($body_content->name)) {
                throw new \InvalidArgumentException('Required fields are missing');
            }

            $addresses = array();
            if (!empty($body_content->address->streetAddress) &&
                !empty($body_content->address->locality) &&
                !empty($body_content->address->postalCode) &&
                !empty($body_content->address->country)) {
                $addresses[] = new Address(
                    new Street($body_content->address->streetAddress),
                    new PostalCode($body_content->address->postalCode),
                    new Locality($body_content->address->locality),
                    Country::fromNative($body_content->address->country)
                );
            }

            $phones = array();
            $emails = array();
            $urls = array();
            if (!empty($body_content->contact)) {
                foreach ($body_content->contact as $contactInfo) {
                    if ($contactInfo->type == 'phone') {
                        $phones[] = $contactInfo->value;
                    } elseif ($contactInfo->type == 'email') {
                        $emails[] = $contactInfo->value;
                    } elseif ($contactInfo->type == 'url') {
                        $urls[] = $contactInfo->value;
                    }
                }
            }

            $organizer_id = $this->editingService->create(
                new Title($body_content->name),
                $addresses,
                $phones,
                $emails,
                $urls
            );

            $response->setData(
                [
                    'organizerId' => $organizer_id,
                    'url' => $this->iriGenerator->iri($organizer_id),
                ]
            );
        } catch (\Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * @param string $organizerId
     * @param string $labelId
     * @return Response
     */
    public function addLabel($organizerId, $labelId)
    {
        $commandId = $this->editingService->addLabel(
            $organizerId,
            new UUID($labelId)
        );

        return JsonResponse::create(['commandId' => $commandId]);
    }

    /**
     * @param string $organizerId
     * @param string $labelId
     * @return Response
     */
    public function removeLabel($organizerId, $labelId)
    {
        $commandId = $this->editingService->removeLabel(
            $organizerId,
            new UUID($labelId)
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
