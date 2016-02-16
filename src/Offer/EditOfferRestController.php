<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String;

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
     * EditOfferRestController constructor.
     * @param OfferEditingServiceInterface $editingServiceInterface
     * @param DeserializerInterface $labelJsonDeserializer
     * @param DeserializerInterface $titleJsonDeserializer
     * @param DeserializerInterface $descriptionJsonDeserializer
     */
    public function __construct(
        OfferEditingServiceInterface $editingServiceInterface,
        DeserializerInterface $labelJsonDeserializer,
        DeserializerInterface $titleJsonDeserializer,
        DeserializerInterface $descriptionJsonDeserializer
    ) {
        $this->editService = $editingServiceInterface;
        $this->labelJsonDeserializer = $labelJsonDeserializer;
        $this->titleJsonDeserializer = $titleJsonDeserializer;
        $this->descriptionJsonDeserializer = $descriptionJsonDeserializer;
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @return JsonResponse
     */
    public function addLabel(Request $request, $cdbid)
    {
        $json = new String($request->getContent());
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
        $commandId = $this->editService->deleteLabel(
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
            new String($request->getContent())
        );

        $commandId = $this->editService->translateTitle(
            $cdbid,
            new \CultuurNet\UDB3\Language($lang),
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
            new String($request->getContent())
        );

        $commandId = $this->editService->translateDescription(
            $cdbid,
            new \CultuurNet\UDB3\Language($lang),
            $description
        );

        return new JsonResponse(
            ['commandId' => $commandId]
        );
    }
}
