<?php

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\UDB3\ImageAsset\ImageUploaderInterface;
use CultuurNet\UDB3\ImageAsset\ImageUploaderService;
use CultuurNet\UDB3\ImageAsset\UploadImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String;

class ImageAssetController
{
    /**
     * @var ImageUploaderInterface
     */
    protected $imageUploader;

    public function __construct(ImageUploaderInterface $imageUploader)
    {
        $this->imageUploader = $imageUploader;
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
}