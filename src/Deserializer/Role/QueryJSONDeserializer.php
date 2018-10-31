<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Role;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Role\ValueObjects\Query;
use ValueObjects\StringLiteral\StringLiteral;

class QueryJSONDeserializer extends JSONDeserializer
{
    public function __construct()
    {
        $assoc = TRUE;
        parent::__construct($assoc);
    }

    /**
     * @param StringLiteral $data
     * @return Query
     */
    public function deserialize(StringLiteral $data): Query
    {
        $data = parent::deserialize($data);
        return new Query($data['query']);
    }
}
