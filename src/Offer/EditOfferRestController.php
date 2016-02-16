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

    /**
     * @param Request $request
     * @param $cdbid
     * @param $lang
     * @return JsonResponse
     */
    public function translateTitle(Request $request, $cdbid, $lang)
    {
        $response = new JsonResponse();

        // TODO json decode this
        $title = $request->request->get('title');
        if (!$title) {
            return new JsonResponse(['error' => "title required"], 400);
        }

        try {
            $commandId = $this->editService->translateTitle(
                $cdbid,
                new \CultuurNet\UDB3\Language($lang),
                new \ValueObjects\String\String($title)
            );

            $response->setData(['commandId' => $commandId]);
        } catch (\Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param $cdbid
     * @param $lang
     * @return JsonResponse
     */
    public function translateDescription(Request $request, $cdbid, $lang)
    {
        $response = new JsonResponse();

        // TODO json decode this
        $description = $request->request->get('description');
        if (!$description) {
            return new JsonResponse(['error' => "description required"], 400);
        }

        try {
            $commandId = $this->editService->translateDescription(
                $cdbid,
                new \CultuurNet\UDB3\Language($lang),
                new \ValueObjects\String\String($request->get('description'))
            );

            $response->setData(['commandId' => $commandId]);
        } catch (\Exception $e) {
            $response->setStatusCode(400);
            $response->setData(['error' => $e->getMessage()]);
        }

        return $response;
    }
}
