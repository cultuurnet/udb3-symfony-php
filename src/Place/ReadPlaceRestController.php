<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\CalendarSummaryV3\CalendarHTMLFormatter;
use CultuurNet\CalendarSummaryV3\CalendarPlainTextFormatter;
use CultuurNet\SearchV3\Serializer\SerializerInterface;
use CultuurNet\SearchV3\ValueObjects\Place;
use CultuurNet\Hydra\PagedCollection;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Place\ReadModel\Lookup\PlaceLookupServiceInterface;
use CultuurNet\UDB3\Symfony\ApiProblemJsonResponseTrait;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use Symfony\Component\HttpFoundation\Request;

class ReadPlaceRestController
{
    const GET_ERROR_NOT_FOUND = 'An error occurred while getting the event with id %s!';
    const GET_ERROR_GONE = 'An error occurred while getting the event with id %s which was removed!';

    use ApiProblemJsonResponseTrait;

    /**
     * @var EntityServiceInterface
     */
    private $service;

    /**
     * @var PlaceLookupServiceInterface
     */
    private $lookupService;

    /**
     * @param EntityServiceInterface $service
     * @param PlaceLookupServiceInterface $lookupService
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EntityServiceInterface $service,
        PlaceLookupServiceInterface $lookupService,
        SerializerInterface $serializer
    ) {
        $this->service = $service;
        $this->lookupService = $lookupService;
        $this->serializer = $serializer;
    }

    /**
     * @param $cdbid
     * @return JsonLdResponse
     */
    public function get($cdbid)
    {
        $response = null;

        $place = $this->service->getEntity($cdbid);

        if ($place) {
            $response = JsonLdResponse::create()
                ->setContent($place);

            $response->headers->set('Vary', 'Origin');
        } else {
            $response = $this->createApiProblemJsonResponseNotFound(self::GET_ERROR_NOT_FOUND, $cdbid);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonLdResponse
     */
    public function getByPostalCode(Request $request)
    {
        // @todo Add & process pagination parameters
        // @todo Validate zipcode
        $zipCode = $request->query->get('zipcode');
        $country = $request->query->get('country');

        if (!$country) {
            $country = 'BE';
        }

        $ids = $this->lookupService->findPlacesByPostalCode($zipCode, $country);

        $members = [];
        if (!empty($ids)) {
            $members = array_map(
                function ($id) {
                    return json_decode($this->service->getEntity($id));
                },
                $ids
            );
        }

        $pagedCollection = new PagedCollection(
            1,
            1000,
            $members,
            count($members)
        );

        return new JsonLdResponse($pagedCollection);
    }

    /**
     * @param string $cdbid
     *
     * @return string
     */
    public function getCalendarSummary($cdbid, Request $request)
    {
        $data = null;
        $response = null;

        $style = ($request->query->get('style') !== null) ? $request->query->get('style') : 'text';
        $langCode = ($request->query->get('langCode') !== null) ? $request->query->get('langCode') : 'nl_BE';
        $hidePastDates = ($request->query->get('hidePast') !== null) ? $request->query->get('hidePast') : false;
        $timeZone = ($request->query->get('timeZone') !== null) ? $request->query->get('timeZone') : 'Europe/Brussels';

        if ($request->query->get('format') !== null) {
            $format = $request->query->get('format');

            $data = $this->service->getEntity($cdbid);
            $place = $this->serializer->deserialize($data, Place::class);

            if ($style === 'html') {
                $calSum = new CalendarHTMLFormatter($langCode, $hidePastDates, $timeZone);
            } else {
                $calSum = new CalendarPlainTextFormatter($langCode, $hidePastDates, $timeZone);
            }

            $response = $calSum->format($place, $format);
        } else {
            $response = $this->createApiProblemJsonResponseNotFound(
                'Please provide a valid calendar summary format', $cdbid
            );
        }

        return $response;
    }
}
