<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\StringLiteral\StringLiteral;

class EditOfferRestController
{
    /**
     * @var OfferEditingServiceInterface
     */
    private $editService;

    /**
     * @var DeserializerInterface
     */
    private $labelJsonDeserializer;

    /**
     * @var DeserializerInterface
     */
    private $titleJsonDeserializer;

    /**
     * @var DeserializerInterface
     */
    private $descriptionJsonDeserializer;

    /**
     * @var DeserializerInterface
     */
    private $priceInfoJsonDeserializer;

    /**
     * @var DeserializerInterface
     */
    private $calendarJsonDeserializer;

    /**
     * @var DeserializerInterface
     */
    private $facilityDeserializer;

    /**
     * EditOfferRestController constructor.
     * @param OfferEditingServiceInterface $editingServiceInterface
     * @param DeserializerInterface $labelJsonDeserializer
     * @param DeserializerInterface $titleJsonDeserializer
     * @param DeserializerInterface $descriptionJsonDeserializer
     * @param DeserializerInterface $priceInfoJsonDeserializer
     * @param DeserializerInterface $calendarJsonDeserializer
     * @param DeserializerInterface $facilityDeserializer
     */
    public function __construct(
        OfferEditingServiceInterface $editingServiceInterface,
        DeserializerInterface $labelJsonDeserializer,
        DeserializerInterface $titleJsonDeserializer,
        DeserializerInterface $descriptionJsonDeserializer,
        DeserializerInterface $priceInfoJsonDeserializer,
        DeserializerInterface $calendarJsonDeserializer,
        DeserializerInterface $facilityDeserializer
    ) {
        $this->editService = $editingServiceInterface;
        $this->labelJsonDeserializer = $labelJsonDeserializer;
        $this->titleJsonDeserializer = $titleJsonDeserializer;
        $this->descriptionJsonDeserializer = $descriptionJsonDeserializer;
        $this->priceInfoJsonDeserializer = $priceInfoJsonDeserializer;
        $this->calendarJsonDeserializer = $calendarJsonDeserializer;
        $this->facilityDeserializer = $facilityDeserializer;
    }

    /**
     * @param $cdbid
     * @param $label
     * @return JsonResponse
     */
    public function addLabel($cdbid, $label)
    {
        $commandId = $this->editService->addLabel(
            $cdbid,
            new Label($label)
        );

        return new JsonResponse(['commandId' => $commandId]);
    }

    /**
     * @deprecated
     *
     * @param Request $request
     * @param $cdbid
     * @return JsonResponse
     */
    public function addLabelFromJsonBody(Request $request, $cdbid)
    {
        $json = new StringLiteral($request->getContent());
        $label = $this->labelJsonDeserializer->deserialize($json);

        $commandId = $this->editService->addLabel(
            $cdbid,
            $label
        );

        return new JsonResponse(['commandId' => $commandId]);
    }

    /**
     * @param $cdbid
     * @param $label
     * @return JsonResponse
     */
    public function removeLabel($cdbid, $label)
    {
        $commandId = $this->editService->removeLabel(
            $cdbid,
            new Label($label)
        );

        return new JsonResponse(['commandId' => $commandId]);
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @param $lang
     * @return JsonResponse
     */
    public function updateTitle(Request $request, $cdbid, $lang)
    {
        $title = $this->titleJsonDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->updateTitle(
            $cdbid,
            new Language($lang),
            $title
        );

        return new JsonResponse(
            ['commandId' => $commandId]
        );
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @param $lang
     * @return JsonResponse
     */
    public function updateDescription(Request $request, $cdbid, $lang)
    {
        $description = $this->descriptionJsonDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->updateDescription(
            $cdbid,
            new Language($lang),
            $description
        );

        return new JsonResponse(
            ['commandId' => $commandId]
        );
    }

    /**
     * @param string $cdbid
     * @param string $typeId
     *
     * @return JsonResponse
     */
    public function updateType($cdbid, $typeId)
    {
        $commandId = $this->editService->updateType($cdbid, new StringLiteral($typeId));
        return new JsonResponse(['commandId' => $commandId]);
    }

    /**
     * @param string $cdbid
     * @param string $themeId
     *
     * @return JsonResponse
     */
    public function updateTheme($cdbid, $themeId)
    {
        $commandId = $this->editService->updateTheme($cdbid, new StringLiteral($themeId));
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
        $facilities = $this->facilityDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->updateFacilities($cdbid, $facilities);

        return new JsonResponse(['commandId' => $commandId]);
    }

    /**
     * @deprecated use the updateFacilities action instead to prevent unknown facilities from entering the system.
     *  This method should be gone once III-2414 is completed.
     *
     * Update the facilities with labels.
     *
     * @param Request $request
     * @param string $cdbid
     * @return JsonResponse
     */
    public function updateFacilitiesWithLabel(Request $request, $cdbid)
    {
        $body_content = json_decode($request->getContent());

        $facilities = array();
        foreach ($body_content->facilities as $facility) {
            $facilities[] = new Facility($facility->id, $facility->label);
        }

        $commandId = $this->editService->updateFacilities($cdbid, $facilities);

        return new JsonResponse(['commandId' => $commandId]);
    }

    /**
     * @param Request $request
     * @param string $cdbid
     *
     * @return JsonResponse
     */
    public function updateCalendar(Request $request, $cdbid)
    {
        $calendar = $this->calendarJsonDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->updateCalendar(
            $cdbid,
            $calendar
        );

        return new JsonResponse(
            ['commandId' => $commandId]
        );
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @return JsonResponse
     */
    public function updatePriceInfo(Request $request, $cdbid)
    {
        $priceInfo = $this->priceInfoJsonDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->updatePriceInfo(
            $cdbid,
            $priceInfo
        );

        return new JsonResponse(
            ['commandId' => $commandId]
        );
    }
}
