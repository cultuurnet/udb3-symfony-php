<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\Deserializer\DeserializerInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;
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
     * @var \CultureFeed_User
     */
    private $user;

    /**
     * @var UsedLabelsMemoryServiceInterface
     */
    private $labelMemory;

    /**
     * @var DeserializerInterface
     */
    private $labelJsonDeserializer;

    /**
     * EditOfferRestController constructor.
     * @param OfferEditingServiceInterface $editingServiceInterface
     * @param \CultureFeed_User $user
     * @param UsedLabelsMemoryServiceInterface $labelMemory
     * @param DeserializerInterface $labelJsonDeserializer
     */
    public function __construct(
        OfferEditingServiceInterface $editingServiceInterface,
        \CultureFeed_User $user,
        UsedLabelsMemoryServiceInterface $labelMemory,
        DeserializerInterface $labelJsonDeserializer
    ) {
        $this->editService = $editingServiceInterface;
        $this->user = $user;
        $this->labelMemory = $labelMemory;
        $this->labelJsonDeserializer = $labelJsonDeserializer;
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @return JsonResponse
     */
    public function addLabel(Request $request, $cdbid)
    {
        $response = new JsonResponse();
        $json = new String($request->getContent());
        $label = $this->labelJsonDeserializer->deserialize($json);

        $commandId = $this->editService->addLabel(
            $cdbid,
            $label
        );

        $this->labelMemory->rememberLabelUsed(
            $this->user->id,
            $label
        );

        $response->setData(['commandId' => $commandId]);

        return $response;
    }

    /**
     * @param $cdbid
     * @param $label
     * @return JsonResponse
     */
    public function removeLabel($cdbid, $label)
    {
        $response = new JsonResponse();

        $commandId = $this->editService->deleteLabel(
            $cdbid,
            new Label($label)
        );

        $response->setData(['commandId' => $commandId]);

        return $response;
    }
}
