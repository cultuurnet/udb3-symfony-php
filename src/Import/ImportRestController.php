<?php

namespace CultuurNet\UDB3\Symfony\Import;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Model\Import\DecodedDocument;
use CultuurNet\UDB3\Model\Import\DocumentImporterInterface;
use Respect\Validation\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportRestController
{
    /**
     * @var DocumentImporterInterface
     */
    private $documentImporter;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @var string
     */
    private $idProperty;

    /**
     * @param DocumentImporterInterface $documentImporter
     * @param UuidGeneratorInterface $uuidGenerator
     * @param IriGeneratorInterface $iriGenerator
     * @param $idProperty
     */
    public function __construct(
        DocumentImporterInterface $documentImporter,
        UuidGeneratorInterface $uuidGenerator,
        IriGeneratorInterface $iriGenerator,
        $idProperty = 'id'
    ) {
        $this->documentImporter = $documentImporter;
        $this->uuidGenerator = $uuidGenerator;
        $this->iriGenerator = $iriGenerator;
        $this->idProperty = $idProperty;
    }

    /**
     * @param Request $request
     * @param string $cdbid
     * @return Response
     */
    public function importWithId(Request $request, $cdbid)
    {
        $json = $this->getJson($request);
        $document = DecodedDocument::fromJson($cdbid, $json);

        $body = $document->getBody();
        $body['@id'] = $this->iriGenerator->iri($cdbid);
        $document = $document->withBody($body);

        $this->documentImporter->import($document);

        return (new JsonResponse())
            ->setData([$this->idProperty => $cdbid])
            ->setPrivate();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function importWithoutId(Request $request)
    {
        $cdbid = $this->uuidGenerator->generate();
        return $this->importWithId($request, $cdbid);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getJson($request)
    {
        $json = $request->getContent();

        if (empty($json)) {
            throw new ValidationException('JSON-LD missing.');
        }

        return $json;
    }
}
