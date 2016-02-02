<?php

namespace CultuurNet\UDB3\Symfony;

use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\UDB3\EntityNotFoundException;
use CultuurNet\UDB3\Media\ImageUploaderInterface;
use CultuurNet\UDB3\Media\MediaManager;
use CultuurNet\UDB3\Media\Serialization\MediaObjectSerializer;
use CultuurNet\UDB3\Media\UploadImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
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
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        ImageUploaderInterface $imageUploader,
        MediaManager $mediaManager,
        SerializerInterface $serializer
    ) {
        $this->imageUploader = $imageUploader;
        $this->mediaManager = $mediaManager;
        $this->serializer = $serializer;
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
        } catch (AggregateNotFoundException $ex) {
            throw new EntityNotFoundException(
                sprintf('Media with id: %s not found.', $id)
            );
        }

        $serializedMediaObject = $this->serializer
            ->serialize($mediaObject, 'json-ld');

        return JsonResponse::create($serializedMediaObject);
    }
}
