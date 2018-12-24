<?php

namespace OrderBundle\Controller;

use AppBundle\Services\TrashRequests\Creator;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use OrderBundle\DTO\FinishRequestDTO;
use OrderBundle\Parsers\Requests\Finish;
use OrderBundle\Services\Finisher;
use OrderBundle\Validators\Requests\FinishApi;

class FinishRestController extends FOSRestController
{
    /**
     * FINISH ORDER
     *
     * ### Response OK ###
     *     {
     *          "id": (string)"",
     *          "updatedAt": (string)""
     *     }
     *
     * ### 400 Bad Request ###
     *     {
     *          "code": 400,
     *          "message": "Parameter 'orderId' of value 'Some no valid value' violated a constraint 'Parameter 'orderId' value, does not match requirements '[0-9]+''"
     *     }
     *
     * @ApiDoc(
     *   section = "Order",
     *   resource = true,
     *   description = "Finish order",
     *   headers={
     *      {
     *          "name"="X-ACCESS-TOKEN",
     *          "description"="Access token",
     *          "required"=true
     *      }
     *   },
     *   parameters={
     *       {"name"="orderID", "dataType"="string", "required"=true, "description"="Order ID"},
     *       {"name"="finishTime", "dataType"="int", "required"=true, "description"="Finish WO in timestamp"},
     *       {"name"="technicianID", "dataType"="int", "required"=true, "description"="Finisher technician ID"},
     *       {"name"="lat", "dataType"="string", "required"=false, "description"="Latitude coordinate"},
     *       {"name"="lng", "dataType"="string", "required"=false, "description"="Longitude coordinate"},
     *       {"name"="comment", "dataType"="string", "required"=false, "description"="Comment"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Validation errors",
     *     401 = "API Key 'token key' does not exist.",
     *   }
     * )
     *
     * @param Request $request
     * @Rest\Put("/####")
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function putFinishAction(Request $request)
    {
        /** @var Finish $restFinishParser */
        $restFinishParser = $this->get('order.request_finish.parser');
        /** @var Creator $trashRequestCreator */
        $trashRequestCreator = $this->get('app.trash_requests.creator.service');
        /** @var Finisher $orderFinisher */
        $orderFinisher = $this->get('order.finisher');
        /** @var FinishApi $finishValidator */
        $finishValidator = $this->get('order.request_finish_api.validator');

        /** @var FinishRequestDTO $finishRequestDTO */
        $finishRequestDTO = $restFinishParser->parse($request);

        if (!$finishValidator->validate($finishRequestDTO)) {
            $messages = implode("\n", $finishValidator->getErrorMessages());
            $trashRequestCreator->add($request, $messages);

            return $this->view(
                ['statusCode' => '601', 'message' => 'Order can not be finished'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $orderFinisher->finish($finishRequestDTO);

        return $this->view(
            [
                'id' => (string)$finishRequestDTO->order->getId(),
                'updatedAt' => $finishRequestDTO->order->getDateUpdate()->getTimestamp()
            ],
            Response::HTTP_OK
        );
    }
}
