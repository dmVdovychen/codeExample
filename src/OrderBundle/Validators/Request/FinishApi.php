<?php

namespace OrderBundle\Validators\Requests;

use AppBundle\Entity\Contractor;
use AppBundle\Entity\Order;
use OrderBundle\DTO\FinishRequestDTO;

class FinishApi extends Finish
{
    /**
     * @param FinishRequestDTO $finishRequestDTO
     * @return bool
     */
    public function validate(FinishRequestDTO $finishRequestDTO)
    {
        if (!$finishRequestDTO->finisher instanceof Contractor) {
            $this->errorMessages[] = "Contractor does not found.";

            return false;
        }

        return parent::validate($finishRequestDTO);
    }
}
