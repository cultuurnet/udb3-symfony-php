<?php

namespace CultuurNet\UDB3\Symfony\Jobs;

use Resque_Job_Status;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReadRestController
{
    public function get($jobId)
    {
        $status = new Resque_Job_Status($jobId);
        return new JsonResponse($status);
    }
}
