<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\Services\WriteResult;
use CultuurNet\UDB3\Label\Services\WriteServiceInterface;
use CultuurNet\UDB3\Symfony\Label\Helper\RequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

        return new JsonResponse($this->writeResultAsArray($writeResult));
    }

    /**
     * @param WriteResult $writeResult
     * @return array
     */
    private function writeResultAsArray(WriteResult $writeResult)
    {
        // TODO: Implement serializable interface on write result?
        return [
            self::COMMAND_ID => $writeResult->getCommandId()->toNative(),
            self::UUID => $writeResult->getUuid()->toNative()
        ];
    }
}