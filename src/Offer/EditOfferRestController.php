<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * EditOfferRestController constructor.
     * @param OfferEditingServiceInterface $editingServiceInterface
     */
    public function __construct(
        OfferEditingServiceInterface $editingServiceInterface,
        \CultureFeed_User $user,
        UsedLabelsMemoryServiceInterface $labelMemory
    ) {
        $this->editService = $editingServiceInterface;
        $this->user = $user;
        $this->labelMemory = $labelMemory;
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @return JsonResponse
     */
    public function addLabel(Request $request, $cdbid)
    {
        $response = new JsonResponse();
        $body_content = json_decode($request->getContent());

        $label = new \CultuurNet\UDB3\Label($body_content->label);
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
            new \CultuurNet\UDB3\Label($label)
        );

        $response->setData(['commandId' => $commandId]);

        return $response;
    }
}
