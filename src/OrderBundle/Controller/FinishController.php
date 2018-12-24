<?php

namespace OrderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use OrderBundle\DTO\FinishRequestDTO;
use AppBundle\Entity\Order;
use Symfony\Component\HttpFoundation\Request;
use OrderBundle\Services\Finisher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use OrderBundle\Validators\Requests\Finish;

class FinishController extends Controller
{
    /**
     * @param Order $order
     * @param Request $request
     * @return RedirectResponse
     */
    public function finishAction(Order $order, Request $request)
    {
        /** @var Finisher $orderFinisher */
        $orderFinisher = $this->get('order.finisher');
        /** @var Session $session */
        $session = $this->get('session');
        /** @var Finish $finishValidator */
        $finishValidator = $this->get('order.request_finish.validator');

        /** @var FinishRequestDTO $finishRequest */
        $finishRequest = new FinishRequestDTO();
        $finishRequest->order = $order;
        $finishRequest->comment = $request->request->get('comment');

        if (!$finishValidator->validate($finishRequest)) {
            $messages = implode("\n", $finishValidator->getErrorMessages());
            $session->getFlashBag()->add('failed', $messages);
        } else {
            $orderFinisher->finish($finishRequest);
        }

        return $this->redirectToRoute('order_view', [
            "order" => $order->getId()
        ]);
    }
}
