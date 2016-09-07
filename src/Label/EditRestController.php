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
    const NAME = 'name';
    const VISIBILITY = 'visibility';
    const PRIVACY = 'privacy';

    const COMMAND_ID = 'commandId';
    const UUID = 'uuid';

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
        $writeResult = $this->writeService->create(
            $this->getName($request),
            $this->getVisibility($request),
            $this->getPrivacy($request)
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
        $commandType = CommandType::createFromRequest($request);
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

    /**
     * @param Request $request
     * @return LabelName
     */
    public function getName(Request $request)
    {
        return new LabelName($this->getParamByName($request, self::NAME));
    }

    /**
     * @param Request $request
     * @return Visibility
     */
    public function getVisibility(Request $request)
    {
        return Visibility::fromNative(
            $this->getParamByName($request, self::VISIBILITY)
        );
    }

    /**
     * @param Request $request
     * @return Privacy
     */
    public function getPrivacy(Request $request)
    {
        return Privacy::fromNative(
            $this->getParamByName($request, self::PRIVACY)
        );
    }

    /**
     * @param Request $request
     * @param string $name
     * @return mixed
     */
    private function getParamByName(Request $request, $name)
    {
        $bodyAsArray = json_decode($request->getContent(), true);
        return $bodyAsArray[$name];
    }
}
