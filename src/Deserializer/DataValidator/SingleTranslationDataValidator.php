<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\DataValidator;

use CultuurNet\Deserializer\DataValidationException;

class SingleTranslationDataValidator implements DataValidatorInterface
{
    /**
     * @var string[]
     */
    private $singleTranslationFields;

    /**
     * @param string[] $singleTranslationFields
     */
    public function __construct(array $singleTranslationFields)
    {
        $this->singleTranslationFields = $singleTranslationFields;
    }


    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $errors = [];

        foreach ($this->singleTranslationFields as $singleTranslationField) {
            if (count(array_keys($data[$singleTranslationField])) > 1) {
                $errors[$singleTranslationField] = "Field has more then one translation.";
            }
        }

        if (!empty($errors)) {
            $exception = new DataValidationException();
            $exception->setValidationMessages($errors);
            throw $exception;
        }
    }
}
