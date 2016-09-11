<?php

namespace CultuurNet\UDB3\Symfony\Jobs;

use ValueObjects\String\String as StringLiteral;

interface JobsStatusFactoryInterface
{
    /**
     * @param StringLiteral $jobId
     * @return JobStatus|null
     */
    public function createFromJobId(StringLiteral $jobId);
}
