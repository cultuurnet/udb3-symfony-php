<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\PriceInfo;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceCategory;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\PriceInfo\PriceInfoItem;
use ValueObjects\Money\Currency;
use ValueObjects\String\String as StringLiteral;

/**
 * @todo Move data validation to a separate class when III-1325 is merged.
 */
class PriceInfoJSONDeserializer extends JSONDeserializer
{
    /**
     * @param StringLiteral $data
     * @return PriceInfo
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        $items = [];

        foreach ($data as $itemData) {
            if (!isset($itemData['category'])) {
                throw new MissingValueException('The category property is required for each priceInfo item.');
            }

            if (!isset($itemData['name']) && $itemData['category'] !== 'base') {
                throw new MissingValueException('The name property is required for each priceInfo item except base.');
            }

            if (!isset($itemData['price'])) {
                throw new MissingValueException('The price property is required for each priceInfo item.');
            }

            if (!isset($itemData['priceCurrency'])) {
                throw new MissingValueException('The priceCurrency property is required for each priceInfo item.');
            }

            $items[] = new PriceInfoItem(
                PriceCategory::fromNative($itemData['category']),
                new StringLiteral($itemData['name']),
                new Price($itemData['price']),
                Currency::fromNative($itemData['priceCurrency'])
            );
        }

        return new PriceInfo($items);
    }
}
