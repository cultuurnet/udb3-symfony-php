<?php

namespace CultuurNet\UDB3\Symfony\Label\Helper;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class RequestHelper
{
    const NAME = 'name';
    const VISIBILITY = 'visibility';
    const PRIVACY = 'privacy';
    const COMMAND = 'command';

    const QUERY = 'query';
    const START = 'start';
    const LIMIT = 'limit';

    /**
     * @param Request $request
     * @return StringLiteral
     */
    public function getName(Request $request)
    {
        return new LabelName($this->getByName($request, self::NAME));
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
     * @return Query
     */
    public function getQuery(Request $request)
    {
        $value = $request->query->get(self::QUERY) !== null ?
            new StringLiteral($request->query->get(self::QUERY)) : new StringLiteral('');

        $offset = $request->query->get(self::START, null) !== null
            ? new Natural($request->query->get(self::START)) : null;

        $limit = $request->query->get(self::LIMIT, null) !== null
            ? new Natural($request->query->get(self::LIMIT)) : null;

        return new Query(
            $value,
            $offset,
            $limit
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
