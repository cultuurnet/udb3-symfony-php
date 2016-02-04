<?php

namespace CultuurNet\UDB3\Symfony\Variations;

use CultuurNet\Hydra\PagedCollection;
use CultuurNet\Hydra\Symfony\PageUrlGenerator;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Variations\ReadModel\Search\CriteriaFromParameterBagFactory;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReadVariationsRestController
{
    /**
     * @var PageUrlGenerator
     */
    private $pageUrlGenerator;

    /**
     * @var RepositoryInterface
     */
    private $searchRepository;

    /**
     * @var DocumentRepositoryInterface
     */
    private $documentRepository;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        RepositoryInterface $searchRepository,
        PageUrlGenerator $pageUrlGenerator
    ) {
        $this->documentRepository = $documentRepository;
        $this->searchRepository = $searchRepository;
        $this->pageUrlGenerator = $pageUrlGenerator;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $factory = new CriteriaFromParameterBagFactory();
        $criteria = $factory->createCriteriaFromParameterBag($request->query);

        $itemsPerPage = 5;
        $pageNumber = intval($request->query->get('page', 0));

        $variationIds = $this->searchRepository->getEventVariations(
            $criteria,
            $itemsPerPage,
            $pageNumber
        );

        $variations = [];
        foreach ($variationIds as $variationId) {
            $document = $this->documentRepository->get($variationId);

            if ($document) {
                $variations[] = $document->getBody();
            }
        }

        $totalItems = $this->searchRepository->countEventVariations(
            $criteria
        );

        return new JsonResponse(
            new PagedCollection(
                $pageNumber,
                $itemsPerPage,
                $variations,
                $totalItems,
                $this->pageUrlGenerator,
                true
            )
        );
    }
}
