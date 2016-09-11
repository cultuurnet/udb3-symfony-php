<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Offer\OfferType;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class PatchOfferRestController
{
    const DOMAIN_MODEL_REGEX = '/.*domain-model=([a-zA-Z]*)/';

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var OfferType
     */
    private $offerType;

    /**
     * PatchOfferRestController constructor.
     * @param OfferType $offerType
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        OfferType $offerType,
        CommandBusInterface $commandBus
    ) {
        $this->offerType = $offerType;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param $offerId
     * @throws Exception
     */
    public function handle(Request $request, $offerId)
    {
        $domainModel = $this->parseDomainModelNameFromRequest($request);
        $commandClass = 'CultuurNet\UDB3\\' . $this->offerType->getValue() . '\Commands\Moderation\\' . $domainModel;

        if (!class_exists($commandClass)) {
            throw new InvalidArgumentException('The command in content-type is not supported.');
        }

        if ($domainModel === 'Reject') {
            $content = json_decode($request->getContent());
            $reason = new StringLiteral($content->reason);

            $command = new $commandClass($offerId, $reason);
        } else {
            $command = new $commandClass($offerId);
        }

        return new JsonResponse([
            'commandId' => $this->commandBus->dispatch($command)
        ]);
    }

    /**
     * @param Request $request
     * @return string
     * @throws Exception
     */
    private function parseDomainModelNameFromRequest(Request $request)
    {
        $contentType = $request->headers->get('Content-Type');
        preg_match(self::DOMAIN_MODEL_REGEX, $contentType, $matches);

        if (!is_array($matches) || !array_key_exists(1, $matches)) {
            throw new Exception('Unable to determine domain-model');
        }

        return $matches[1];
    }
}
