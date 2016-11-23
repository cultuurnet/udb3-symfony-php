<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

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
     * EditOfferRestController constructor.
     * @param OfferEditingServiceInterface $editingServiceInterface
     * @param DeserializerInterface $labelJsonDeserializer
     * @param DeserializerInterface $titleJsonDeserializer
     * @param DeserializerInterface $descriptionJsonDeserializer
     * @param DeserializerInterface $priceInfoJsonDeserializer
     */
    public function __construct(
        OfferEditingServiceInterface $editingServiceInterface,
        DeserializerInterface $labelJsonDeserializer,
        DeserializerInterface $titleJsonDeserializer,
        DeserializerInterface $descriptionJsonDeserializer,
        DeserializerInterface $priceInfoJsonDeserializer
    ) {
        $this->editService = $editingServiceInterface;
        $this->labelJsonDeserializer = $labelJsonDeserializer;
        $this->titleJsonDeserializer = $titleJsonDeserializer;
        $this->descriptionJsonDeserializer = $descriptionJsonDeserializer;
        $this->priceInfoJsonDeserializer = $priceInfoJsonDeserializer;
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @return JsonResponse
     */
    public function addLabel(Request $request, $cdbid)
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
    public function translateTitle(Request $request, $cdbid, $lang)
    {
        $title = $this->titleJsonDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->translateTitle(
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
    public function translateDescription(Request $request, $cdbid, $lang)
    {
        $description = $this->descriptionJsonDeserializer->deserialize(
            new StringLiteral($request->getContent())
        );

        $commandId = $this->editService->translateDescription(
            $cdbid,
            new Language($lang),
            $description
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
