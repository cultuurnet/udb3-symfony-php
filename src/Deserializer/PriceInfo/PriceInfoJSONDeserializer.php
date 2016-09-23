<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\PriceInfo;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\PriceInfo\Tariff;
use ValueObjects\Money\Currency;
use ValueObjects\String\String as StringLiteral;

class PriceInfoJSONDeserializer extends JSONDeserializer
{
    /**
     * @param StringLiteral $data
     * @return PriceInfo
     *
     * @throws MissingValueException
     * @throws \Exception
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        // @todo Move data validation to a separate DataValidator class when III-1325 is merged.
        $basePrices = 0;

        foreach ($data as $itemData) {
            if (!isset($itemData['category'])) {
                throw new MissingValueException('The category property is required for each priceInfo item.');
            }

            if (!isset($itemData['name']) && $itemData['category'] !== 'base') {
                throw new MissingValueException('The name property is required for each priceInfo item (except base).');
            }

            if (!isset($itemData['price'])) {
                throw new MissingValueException('The price property is required for each priceInfo item.');
            }

            if (!isset($itemData['priceCurrency'])) {
                throw new MissingValueException('The priceCurrency property is required for each priceInfo item.');
            }

            if ($itemData['category'] == 'base') {
                $basePrices++;
            }
        }

        if ($basePrices > 1) {
            throw new \Exception('priceInfo should not contain more than one item with the "base" category.');
        }
        if ($basePrices < 1) {
            throw new \Exception('priceInfo should contain one item with the "base" category.');
        }
        // end @todo

        $basePrice = null;
        $tariffs = [];

        foreach ($data as $itemData) {
            if ($itemData['category'] == 'base') {
                $basePrice = new BasePrice(
                    new Price($itemData['price']),
                    Currency::fromNative($itemData['priceCurrency'])
                );
            } else {
                $tariffs[] = new Tariff(
                    new StringLiteral($itemData['name']),
                    new Price($itemData['price']),
                    Currency::fromNative($itemData['priceCurrency'])
                );
            }
        }

        $priceInfo = new PriceInfo($basePrice);

        foreach ($tariffs as $tariff) {
            $priceInfo = $priceInfo->withExtraTariff($tariff);
        }

        return $priceInfo;
    }
}
