<?php

namespace OrderBundle\Services;

use AppBundle\Entity\EntityManager\OrderManager;
use AppBundle\Entity\OrderStatus;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\Order;
use AppBundle\Entity\Repository\OrderStatusRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\OrderLogType;
use OrderBundle\DTO\FinishRequestDTO;
use OrderBundle\Exceptions\LogRecordTypeCantBeNull;
use OrderBundle\Validators\AvailableStatus;

class Finisher
{
    const FINISHED_STATUS_ALIAS = 'verify_results';

    /** @var ObjectManager */
    private $objectManager;
    /** @var ContainerInterface  */
    private $container;

    /** @var OrderManager */
    private $orderManager;
    /** @var LogCreator */
    private $orderLogCreator;
    /** @var LogMessage $logMessage */
    private $logMessage;

    /** @var OrderStatusRepository */
    private $orderStatusRepository;
    /** @var OrderStatus */
    private $finishedStatus;
    /** @var Session */
    private $session;
    /** @var AvailableStatus */
    private $availableStatusService;

    /**
     * Finisher constructor.
     *
     * @param ObjectManager $objectManager
     * @param ContainerInterface $container
     */
    public function __construct(ObjectManager $objectManager, ContainerInterface $container)
    {
        $this->objectManager = $objectManager;
        $this->container = $container;

        $this->orderManager = $this->container->get('app.order.manager');
        $this->orderLogCreator = $this->container->get('order.log_creator');
        $this->logMessage = $this->container->get('order.log_message');

        $this->orderStatusRepository = $this->objectManager->getRepository("AppBundle:OrderStatus");
        $this->finishedStatus = $this->orderStatusRepository->findOneBy(['alias' => self::FINISHED_STATUS_ALIAS]);
        $this->session = $this->container->get('session');
        $this->availableStatusService = $this->container->get('order.available_status.validator');
    }

    /**
     * @param FinishRequestDTO $finishRequest
     */
    public function finish(FinishRequestDTO $finishRequest)
    {
        /** @var Order $order */
        $order = $finishRequest->workorder;
        $order->setFinishTime(new \DateTime());
        if ($this->availableStatusService->canChangeStatus($order->getStatus(), $this->finishedStatus)) {
            $order->setStatus($this->finishedStatus);
        }

        $this->orderManager->update($order);

        try {
            $this->orderLogCreator->createRecord($order, [
                'type' => OrderLogType::TYPE_VERIFY_RESULTS,
                'message' => $this->logMessage->makeByLogType(OrderLogType::TYPE_VERIFY_JOB_RESULTS),
                'comment' => $finishRequest->comment
            ]);
        } catch (LogRecordTypeCantBeNull $exception) {
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        }
    }
}
