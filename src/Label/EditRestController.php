<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\Services\WriteServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;

class EditRestController
{
    /**
     * @var WriteServiceInterface
     */
    private $writeService;

    /**
     * EditRestController constructor.
     * @param WriteServiceInterface $writeService
     */
    public function __construct(WriteServiceInterface $writeService)
    {
        $this->writeService = $writeService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $bodyAsArray = json_decode($request->getContent(), true);

        $writeResult = $this->writeService->create(
            new LabelName($bodyAsArray['name']),
            Visibility::fromNative($bodyAsArray['visibility']),
            Privacy::fromNative($bodyAsArray['privacy'])
        );

        return new JsonResponse($writeResult);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function patch(Request $request, $id)
    {
        $bodyAsArray = json_decode($request->getContent(), true);
        $commandType = CommandType::fromNative($bodyAsArray['command']);

        $id = new UUID($id);

        switch ($commandType) {
            case CommandType::MAKE_VISIBLE():
                $writeResult = $this->writeService->makeVisible($id);
                break;
            case CommandType::MAKE_INVISIBLE():
                $writeResult = $this->writeService->makeInvisible($id);
                break;
            case CommandType::MAKE_PUBLIC():
                $writeResult = $this->writeService->makePublic($id);
                break;
            case CommandType::MAKE_PRIVATE():
                $writeResult = $this->writeService->makePrivate($id);
                break;
        }

        return new JsonResponse($writeResult);
    }
}
