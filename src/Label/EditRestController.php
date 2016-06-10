<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\Services\WriteServiceInterface;
use CultuurNet\UDB3\Symfony\Label\Helper\CommandType;
use CultuurNet\UDB3\Symfony\Label\Helper\RequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;

class EditRestController
{
    const COMMAND_ID = 'commandId';
    const UUID = 'uuid';

    /**
     * @var WriteServiceInterface
     */
    private $writeService;

    /**
     * @var RequestHelper
     */
    private $requestHelper;

    /**
     * EditRestController constructor.
     * @param WriteServiceInterface $writeService
     * @param RequestHelper $requestHelper
     */
    public function __construct(
        WriteServiceInterface $writeService,
        RequestHelper $requestHelper
    ) {
        $this->writeService = $writeService;
        $this->requestHelper = $requestHelper;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $writeResult = $this->writeService->create(
            $this->requestHelper->getName($request),
            $this->requestHelper->getVisibility($request),
            $this->requestHelper->getPrivacy($request)
        );

        return new JsonResponse($writeResult);
    }

    /**
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function patch(Request $request, $uuid)
    {
        $commandType = $this->requestHelper->getCommandType($request);
        $uuid = new UUID($uuid);

        switch ($commandType) {
            case CommandType::MAKE_VISIBLE():
                $writeResult = $this->writeService->makeVisible($uuid);
                break;
            case CommandType::MAKE_INVISIBLE():
                $writeResult = $this->writeService->makeInvisible($uuid);
                break;
            case CommandType::MAKE_PUBLIC():
                $writeResult = $this->writeService->makePublic($uuid);
                break;
            case CommandType::MAKE_PRIVATE():
                $writeResult = $this->writeService->makePrivate($uuid);
                break;
        }

        return new JsonResponse($writeResult);
    }
}
