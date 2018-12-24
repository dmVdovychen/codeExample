<?php

namespace OrderBundle\Parsers\Requests;

use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Repository\ContractorRepository;
use AppBundle\Entity\Repository\OrderRepository;
use AppBundle\Services\CoordinateService;
use Symfony\Component\HttpFoundation\Request;
use OrderBundle\DTO\FinishRequestDTO;

class Finish
{
    /** @var OrderRepository */
    private $orderRepository;
    /** @var ContractorRepository */
    private $contractorRepository;
    /** @var CoordinateService */
    private $coordinateService;

    /**
     * Finish constructor.
     *
     * @param ObjectManager $objectManager
     * @param CoordinateService $coordinateService
     */
    public function __construct(ObjectManager $objectManager, CoordinateService $coordinateService)
    {
        $this->coordinateService = $coordinateService;

        $this->orderRepository = $objectManager->getRepository('AppBundle:Order');
        $this->contractorRepository = $objectManager->getRepository('AppBundle:Contractorr');
    }

    /**
     * @param Request $request
     *
     * @return FinishRequestDTO
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function parse(Request $request)
    {
        $params = $request->request->all();
        $finishRequestDTO = new FinishRequestDTO();

        if (isset($params["orderID"])) {
            $finishRequestDTO->order = $this->orderRepository->find($params["orderID"]);
        }

        if (isset($params["technicianID"])) {
            $finishRequestDTO->finisher = $this->contractorRepository->find($params["technicianID"]);
        }

        if (isset($params["comment"])) {
            $finishRequestDTO->comment = $params["comment"];
        }

        $lat = $params['lat'] ?? 0;
        $lng = $params['lng'] ?? 0;
        $finishRequestDTO->coordinates = $this->coordinateService->getCoordinate(trim($lat), trim($lng));

        return $finishRequestDTO;
    }
}
