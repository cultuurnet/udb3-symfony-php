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
     * EditOfferRestController constructor.
     * @param OfferEditingServiceInterface $editingServiceInterface
     * @param DeserializerInterface $labelJsonDeserializer
     */
    public function __construct(
        OfferEditingServiceInterface $editingServiceInterface,
        DeserializerInterface $labelJsonDeserializer
    ) {
        $this->editService = $editingServiceInterface;
        $this->labelJsonDeserializer = $labelJsonDeserializer;
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
}
