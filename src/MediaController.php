<?php

namespace CultuurNet\UDB3\Symfony;

use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Media\ImageUploaderInterface;
use CultuurNet\UDB3\Media\MediaManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class MediaController
{
    /**
     * @var ImageUploaderInterface
     */
    protected $imageUploader;

    /**
     * @var MediaManager;
     */
    protected $mediaManager;

    /**
     * @param ImageUploaderInterface $imageUploader
     * @param MediaManager $mediaManager
     */
    public function __construct(
        ImageUploaderInterface $imageUploader,
        MediaManager $mediaManager
    ) {
        $this->imageUploader = $imageUploader;
        $this->mediaManager = $mediaManager;
    }

    public function upload(Request $request)
    {
        if (!$request->files->has('file')) {
            return new JsonResponse(['error' => "file required"], 400);
        }

        $description = $request->request->get('description');
        $copyrightHolder = $request->request->get('copyrightHolder');

        if (!$description) {
            return new JsonResponse(['error' => "description required"], 400);
        }

        if (!$copyrightHolder) {
            return new JsonResponse(['error' => "copyright holder required"], 400);
        }

        $response = new JsonResponse();
        $file = $request->files->get('file');

        $commandId = $this->imageUploader->upload(
            $file,
            new String($description),
            new String($copyrightHolder)
        );

        $response->setData(['commandId' => $commandId]);

        return $response;

    }

    public function get(Request $request, $id)
    {
        try {
            $mediaObject = $this->mediaManager->get(new UUID($id));
        } catch(AggregateNotFoundException $ex) {
            throw new EntityNotFoundException(
                sprintf('Media with id: %s not found.', $id)
            );
        }

        return JsonResponse::create($mediaObject->toJsonLd());
    }
}
