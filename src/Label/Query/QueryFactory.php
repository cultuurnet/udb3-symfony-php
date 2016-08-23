<?php

namespace CultuurNet\UDB3\Symfony\Label\Query;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class QueryFactory implements QueryFactoryInterface
{
    const QUERY = 'query';
    const START = 'start';
    const LIMIT = 'limit';

    /**
     * @param Request $request
     * @return Query
     */
    public function createFromRequest(Request $request)
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
}
