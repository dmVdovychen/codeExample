<?php

namespace OrderBundle\Validators;

use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Repository\OrderStatusRepository;
use \AppBundle\Entity\OrderStatus;

class AvailableStatus
{
    /** @var  ObjectManager */
    private $objectManager;
    /** @var  OrderStatusRepository */
    private $orderStatusRepository;

    /** @var array $errorMessages */
    protected $errorMessages = [];

    /**
     * AvailableStatus constructor.
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        $this->orderStatusRepository = $this->objectManager->getRepository("AppBundle:OrderStatus");
    }

    /**
     * @param OrderStatus $orderStatus
     * @param OrderStatus $changeableStatusAlias
     * @return bool
     */
    public function canChangeStatus(OrderStatus $orderStatus, OrderStatus $changeableStatusAlias)
    {
        if (in_array($orderStatus, $changeableStatusAlias->getAvailableStatuses()->toArray())) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return array_merge(["<b>You can not do this action</b>.<br>"], $this->errorMessages);
    }
}
