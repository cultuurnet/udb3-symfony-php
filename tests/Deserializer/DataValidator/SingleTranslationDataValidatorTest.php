<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\DataValidator;

use CultuurNet\Deserializer\DataValidationException;

class SingleTranslationDataValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_throws_a_validation_exception_when_multiple_translations_are_provided()
    {
        $singleTranslationDataValidator = new SingleTranslationDataValidator(['name', 'address']);

        $data = [
            'name' => [
                'nl' => 'Titel',
            ],
            'address' => [
                'nl' => 'Adres',
                'en' => 'Address',
            ],
            'url' => [
                'nl' => 'www.domain.be',
                'en' => 'www.domain.uk',
            ]
        ];

        try {
            $singleTranslationDataValidator->validate($data);
            $this->fail('Did not catch expected DataValidationException.');
        } catch (\Exception $exception) {
            /* @var DataValidationException $exception */
            $this->assertInstanceOf(DataValidationException::class, $exception);
            $this->assertEquals(
                [
                    'address' => 'Field has more then one translation.'
                ],
                $exception->getValidationMessages()
            );
        }
    }
}
