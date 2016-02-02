<?php

namespace CultuurNet\UDB3\Symfony;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonLdResponse extends JsonResponse
{
    public function __construct($data = null, $status = 200, $headers = array())
    {
        $headers += array(
          'Content-Type' => 'application/ld+json',
        );

        parent::__construct(
            $data,
            $status,
            $headers
        );
    }
}
