<?php

namespace CultuurNet\UDB3\Symfony\JSONLD;

use CultuurNet\UDB3\Symfony\JsonLdResponse;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ContextController
{
    const DEFAULT_BASE_PATH = 'https://io.uitdatabank.be';

    /**
     * @var Url
     */
    protected $basePath;

    /**
     * @var StringLiteral
     */
    protected $fileDirectory;

    /**
     * ContextController constructor.
     * @param StringLiteral $fileDirectory
     */
    public function __construct(
        StringLiteral $fileDirectory
    ) {
        $this->fileDirectory = $fileDirectory;
    }

    /**
     * @param Url $basePath
     * @return ContextController
     */
    public function withCustomBasePath(Url $basePath)
    {
        $controller = clone $this;

        $controller->basePath = Url::fromNative(
            rtrim((string) $basePath, '/')
        );

        return $controller;
    }

    /**
     * @param string $entityName
     *
     * @return JsonLdResponse
     */
    public function get($entityName)
    {
        $entityType = EntityType::fromNative($entityName);
        return $this->getContext($entityType);
    }

    public function entryPoint()
    {
        $jsonData = (object) [
            "@context" => "https://io.uitdatabank.be/contexts/EntryPoint",
            "@id" => "https://io.uitdatabank.be/contexts/",
            "@type" => "EntryPoint",
            "places" => "https://io.uitdatabank.be/places/",
            "organizers" => "https://io.uitdatabank.be/organizers/",
            "events" => "https://io.uitdatabank.be/events/"
        ];

        if ($this->basePath) {
            $this->replaceJsonPropertyBasePath($jsonData, '@context', $this->basePath);
            $this->replaceJsonPropertyBasePath($jsonData, '@id', $this->basePath);
            $this->replaceJsonPropertyBasePath($jsonData, 'places', $this->basePath);
            $this->replaceJsonPropertyBasePath($jsonData, 'organizers', $this->basePath);
            $this->replaceJsonPropertyBasePath($jsonData, 'events', $this->basePath);
        }

        return new JsonLdResponse($jsonData);
    }

    /**
     * @param EntityType $entityType
     *  The entity type that you want the context for.
     *
     * @return JsonLdResponse
     */
    private function getContext(EntityType $entityType)
    {
        $entityFilePath = $this->fileDirectory . $entityType->toNative() . '.jsonld';

        $jsonData = json_decode($this->getEntityFile($entityFilePath));

        if ($this->basePath) {
            $this->replaceJsonContextPropertyBasePath($jsonData, 'udb', $this->basePath);
        }

        return new JsonLdResponse($jsonData);
    }

    /**
     * @param object $jsonData
     *  The json object that should have its base path replaced.
     * @param string $propertyName
     *  The name of the property where you want to replace the base path.
     * @param Url $basePath
     *  The new base path.
     */
    private function replaceJsonPropertyBasePath($jsonData, $propertyName, Url $basePath)
    {
        if (property_exists($jsonData, $propertyName)) {
            $jsonData
                ->{$propertyName} = str_replace(
                    self::DEFAULT_BASE_PATH,
                    (string) $basePath,
                    $jsonData->{$propertyName}
                );
        }
    }

    /**
     * @param object $jsonData
     *  The json object that should have its base path replaced.
     * @param string $propertyName
     *  The name of the property where you want to replace the base path.
     * @param Url $basePath
     *  The new base path.
     */
    private function replaceJsonContextPropertyBasePath($jsonData, $propertyName, Url $basePath)
    {
        if (property_exists($jsonData->{'@context'}, $propertyName)) {
            $jsonData
                ->{'@context'}
                ->{$propertyName} = str_replace(
                    self::DEFAULT_BASE_PATH,
                    (string)$basePath,
                    $jsonData->{'@context'}->{$propertyName}
                );
        }
    }

    /**
     * @param $path
     * @return string
     */
    private function getEntityFile($path)
    {
        return file_get_contents($path);
    }
}
