<?php

namespace CultuurNet\UDB3\Symfony\Media;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\ImageUploaderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class EditMediaRestController
{
    /**
     * @var ImageUploaderInterface
     */
    private $imageUploader;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    public function __construct(
        ImageUploaderInterface $imageUploader,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->imageUploader = $imageUploader;

        $this->uuidGenerator = $uuidGenerator;
    }

    public function upload(Request $request)
    {
        if (!$request->files->has('file')) {
            return new JsonResponse(['error' => "file required"], 400);
        }

        $description = $request->request->get('description');
        $copyrightHolder = $request->request->get('copyrightHolder');
        $language = $request->request->get('language');

        if (!$description) {
            return new JsonResponse(['error' => "description required"], 400);
        }

        if (!$copyrightHolder) {
            return new JsonResponse(['error' => "copyright holder required"], 400);
        }

        if (!$language) {
            return new JsonResponse(['error' => "language required"], 400);
        }

        $response = new JsonResponse();
        $file = $request->files->get('file');

        $imageId = new UUID($this->uuidGenerator->generate());

        $commandId = $this->imageUploader->upload(
            $imageId,
            $file,
            new StringLiteral($description),
            new StringLiteral($copyrightHolder),
            new Language($language)
        );

        $response->setData(
            [
                'commandId' => $commandId,
                'imageId' => $imageId->toNative(),
            ]
        );

        return $response;
    }
}
