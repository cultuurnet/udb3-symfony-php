<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\UDB3\Category;
use ValueObjects\StringLiteral\StringLiteral;

interface OfferFacilityResolverInterface
{
    /**
     * @param StringLiteral $typeId
     * @return Category
     */
    public function byId(StringLiteral $typeId);
}