<?php

namespace OrderBundle\Validators\Requests;

use AppBundle\Entity\Order;
use OrderBundle\DTO\FinishRequestDTO;
use OrderBundle\Validators\AvailableActions;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Finish
{
    const FINISHED_STATUS_ALIAS = 'verify_results';

    /** @var ContainerInterface  */
    private $container;
    /** @var AvailableActions */
    private $availableActionsService;

    /** @var array $errorMessages */
    protected $errorMessages = [];

    /**
     * Finish constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->availableActionsService = $this->container->get('order.available_actions.validator');
    }

    /**
     * @param FinishRequestDTO $finishRequestDTO
     * @return bool
     */
    public function validate(FinishRequestDTO $finishRequestDTO)
    {
        if (!$finishRequestDTO->workorder instanceof Workorder) {
            $this->errorMessages[] = "Workorder does not found";

            return false;
        }

        if ($finishRequestDTO->order instanceof Order) {
            if (!$this->availableActionsService->validate(
                $finishRequestDTO->order->getStatus(),
                self::FINISHED_STATUS_ALIAS
            )) {
                $this->errorMessages[] = "Order with this status can not be finished";

                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return array_merge(["<b>Order can not be finished</b>.<br>"], $this->errorMessages);
    }
}
