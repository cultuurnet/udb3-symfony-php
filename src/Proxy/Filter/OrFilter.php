<?php

namespace CultuurNet\UDB3\Symfony\Proxy\Filter;

use Psr\Http\Message\RequestInterface;

class OrFilter implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * AndFilter constructor.
     * @param FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @inheritdoc
     */
    public function matches(RequestInterface $request)
    {
        foreach ($this->filters as $filter) {
            $matches = $filter->matches($request);

            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
