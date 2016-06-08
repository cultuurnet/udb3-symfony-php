<?php

namespace CultuurNet\UDB3\Symfony\Label\Helper;

use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class RequestHelper
{
    const NAME = 'name';
    const VISIBILITY = 'visibility';
    const PRIVACY = 'privacy';
    const COMMAND = 'command';

    /**
     * @param Request $request
     * @return StringLiteral
     */
    public function getName(Request $request)
    {
        return new StringLiteral($this->getByName($request, self::NAME));
    }

    /**
     * @param Request $request
     * @return Visibility
     */
    public function getVisibility(Request $request)
    {
        return Visibility::fromNative(
            $this->getByName($request, self::VISIBILITY)
        );
    }

    /**
     * @param Request $request
     * @return Privacy
     */
    public function getPrivacy(Request $request)
    {
        return Privacy::fromNative(
            $this->getByName($request, self::PRIVACY)
        );
    }

    /**
     * @param Request $request
     * @return CommandType
     */
    public function getCommandType(Request $request)
    {
        return CommandType::fromNative(
            $this->getByName($request, self::COMMAND)
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getBodyAsArray(Request $request)
    {
        return json_decode($request->getContent(), true);
    }

    /**
     * @param Request $request
     * @param string $name
     * @return mixed
     */
    private function getByName(Request $request, $name)
    {
        $bodyAsArray = $this->getBodyAsArray($request);
        return $bodyAsArray[$name];
    }
}
