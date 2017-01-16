<?php

namespace CultuurNet\UDB3\Symfony\Media;

use CultuurNet\UDB3\Media\ImageUploaderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\StringLiteral\StringLiteral;

class EditMediaRestController
{
    /**
     * @var ImageUploaderInterface
     */
    protected $imageUploader;

    public function __construct(
        ImageUploaderInterface $imageUploader
    ) {
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
            new StringLiteral($description),
            new StringLiteral($copyrightHolder)
        );

        $response->setData(['commandId' => $commandId]);

        return $response;
    }
}
