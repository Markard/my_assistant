<?php namespace MyAssistant\BudgetBundle\Controller;

use Carbon\Carbon;
use Doctrine\Common\Inflector\Inflector;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\BudgetBundle\Entity\Purchase;
use MyAssistant\BudgetBundle\Handler\PurchaseHandler;
use MyAssistant\CoreBundle\Exception\Api\CustomApiException;
use MyAssistant\CoreBundle\Exception\Api\FormValidationException;
use MyAssistant\CoreBundle\Exception\Api\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class PurchaseController extends FOSRestController implements ClassResourceInterface
{
    const DIMENSION_DAY = 'day';
    const DIMENSION_PURCHASE = 'purchase';

    /**
     * Return list of user purchases.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Return list of user purchases.",
     *  filters={
     *      {
     *          "name"="limit",
     *          "dataType"="integer",
     *          "required"=false,
     *          "default"=10,
     *          "description"="Number of items per page. Min is 1 and max is 100."
     *      },
     *      {
     *          "name"="page",
     *          "dataType"="integer",
     *          "required"=false,
     *          "default"=1,
     *          "description"="Requested page. Min is 1. If you exceed maximum page you will get information from last
     *     page."
     *      },
     *      {
     *          "name"="sort",
     *          "dataType"="string",
     *          "required"=false,
     *          "default"="bought_at",
     *          "description"="The name of the field that will be used to sort. If you use not existing sort field then
     *     sorting happens by default value."
     *      },
     *      {
     *          "name"="direction",
     *          "dataType"="string",
     *          "pattern"="ASC|DESC",
     *          "required"=false,
     *          "default"="DESC",
     *          "description"="Sort direction."
     *      },
     *      {
     *          "name"="date",
     *          "dataType"="string",
     *          "pattern"="Y-m (2015-01)",
     *          "required"=false,
     *          "description"="Date on which fetch should occur."
     *      },
     *      {
     *          "name"="dimension",
     *          "dataType"="string",
     *          "pattern"="day|purchase",
     *          "required"=false,
     *          "default"="purchase",
     *          "description"="This variable allow to group result. If you use purchase dimension then result will not
     *     be grouped. If you select day dimension result will be grouped by day."
     *      },
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully got list of data. Response will be like:",
     *          "{'total_count' => ...,'num_items_per_page' => ..., 'page' => ..., 'items' => [..., {",
     *          "'id' => ..., 'title' => ..., 'amount' => ..., 'price' => ..., 'bought_at' => ...",
     *          "}, ...]}"
     *      },
     *      401="You are not authenticated.",
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $limit = $request->query->getInt('limit', 10);
        if ($limit === 0) {
            $limit = 10;
        } else {
            if ($limit > 100) {
                $limit = 100;
            }
        }

        $page = $request->query->getInt('page', 1);
        if ($page === 0) {
            $page = 1;
        }

        $sort = Inflector::camelize($request->query->get('sort', 'bought_at'));
        $orderBy = strtoupper($request->query->getAlpha('direction', 'DESC'));
        if (!in_array($orderBy, ['DESC', 'ASC'])) {
            $orderBy = 'DESC';
        }

        //Filters
        $filters = [];
        if ($date = $this->getGlobalDateFromRequest($request)) {
            $filters['startDate'] = $date->firstOfMonth()->format('Y-m-d');
            $filters['endDate'] = $date->lastOfMonth()->format('Y-m-d');
        }

        /** @var PurchaseHandler $handler */
        $handler = $this->container->get('budget_spending.purchase.handler');
        $dimension = $request->query->getAlpha('dimension', self::DIMENSION_PURCHASE);

        switch ($dimension) {
            case self::DIMENSION_DAY:
                $purchases = $handler->allGropedByDays($limit, $page, $sort, $orderBy, $filters);
                break;
            case self::DIMENSION_PURCHASE:
            default:
                $purchases = $handler->all($limit, $page, $sort, $orderBy, $filters);
        }

        return $this->handleView($this->view($purchases));
    }

    /**
     * @param Request $request
     *
     * @return Carbon|null
     * @throws CustomApiException
     */
    protected function getGlobalDateFromRequest(Request $request)
    {
        if ($date = $request->query->get('date')) {
            try {
                list($year, $month) = explode('-', $date);
                $date = Carbon::create((int)$year, (int)$month, 1);
            } catch (\Exception $e) {
                throw new CustomApiException('Invalid date parameter. Date format should be Y-m format.');
            }
        }

        return $date;
    }

    /**
     * Return information about special purchase.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Return information about special purchase.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Purchase identity."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully got list of data. Response will be like:",
     *          "{'id' => ..., 'title' => ..., 'amount' => ..., 'price' => ..., 'bought_at' => ...}"
     *      },
     *      401="You are not authenticated.",
     *      404="Purchase not found.",
     *  }
     * )
     *
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleView($this->view($this->getOr404($id)));
    }

    /**
     * @param $id
     *
     * @return Purchase
     *
     * @throws NotFoundException
     */
    protected function getOr404($id)
    {
        if (!($purchase = $this->container->get('budget_spending.purchase.handler')->get($id))) {
            throw new NotFoundException("Purchase with id: $id  was not found.");
        }

        return $purchase;
    }

    /**
     * Create purchase for current user.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Create purchase for current user.",
     *  parameters={
     *      {
     *          "name"="title",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Purchase title. Min is 1 symbol and max is 255."
     *      },
     *      {
     *          "name"="amount",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="Purchase amount. Min is 1 and max is 9999999."
     *      },
     *      {
     *          "name"="price",
     *          "dataType"="decimal",
     *          "required"=true,
     *          "format"="0.00",
     *          "description"="Purchase amount. Min is 0 and max is 9999999999."
     *      },
     *      {
     *          "name"="bought_at",
     *          "dataType"="date",
     *          "required"=true,
     *          "format"="Y-m-d",
     *          "description"="Purchase date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully create purchase. Response will be like:",
     *          "{'message' => 'You successfully created purchase.', 'data' => {'id' => ..., 'title' => ..., 'amount'
     *     => ..., 'price' => ..., 'bought_at' => ...}}"
     *      },
     *      400={
     *          "Validation fails or you exceed your daily limit. User can't create more than 100 purchases per day.  Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      },
     *      401="You are not authenticated.",
     *      404="Purchase not found.",
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws FormValidationException
     * @throws CustomApiException
     */
    public function postAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getPurchasesPerDay() >= $this->getParameter('purchases_limit_per_day')) {
            throw new CustomApiException("You exceed your daily limit. You can't create purchase today.");
        }

        /** @var Purchase $purchase */
        $purchase = $this->container->get('budget_spending.purchase.handler')->post(
            $request->request->all()
        );

        return View::create([
            'message' => 'You successfully created purchase.',
            'data' => $purchase
        ], Codes::HTTP_CREATED, [
            'Location' => $this->generateUrl(
                'get_purchase', ['id' => $purchase->getId()],
                true // absolute
            )
        ]);
    }

    /**
     * Update all fields in record.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Update all fields in record.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Purchase identity."
     *      }
     *  },
     *  parameters={
     *      {
     *          "name"="title",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Purchase title. Min is 1 symbol and max is 255."
     *      },
     *      {
     *          "name"="amount",
     *          "dataType"="integer",
     *          "required"=true,
     *          "description"="Purchase amount. Min is 1 and max is 9999999."
     *      },
     *      {
     *          "name"="price",
     *          "dataType"="decimal",
     *          "required"=true,
     *          "format"="0.00",
     *          "description"="Purchase amount. Min is 0 and max is 9999999999."
     *      },
     *      {
     *          "name"="bought_at",
     *          "dataType"="date",
     *          "required"=true,
     *          "format"="Y-m-d",
     *          "description"="Purchase date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully updated purchase. Response will be like:",
     *          "{'message' => 'You successfully updated purchase.', 'data' => {'id' => ..., 'title' => ..., 'amount'
     *     => ..., 'price' => ..., 'bought_at' => ...}}"
     *      },
     *      400={
     *          "Validation fails. Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      },
     *      401="You are not authenticated.",
     *      404="Purchase not found.",
     *  }
     * )
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     *
     * @throws FormValidationException
     */
    public function putAction($id, Request $request)
    {
        /** @var PurchaseHandler $handler */
        $handler = $this->container->get('budget_spending.purchase.handler');
        $purchase = $handler->put($this->getOr404($id), $request->request->all());

        return View::create([
            'message' => 'You successfully updated purchase.',
            'data' => $purchase
        ], Codes::HTTP_OK);
    }

    /**
     * Update some fields in record.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Update some fields in record.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Purchase identity."
     *      }
     *  },
     *  parameters={
     *      {
     *          "name"="title",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Purchase title. Min is 1 symbol and max is 255."
     *      },
     *      {
     *          "name"="amount",
     *          "dataType"="integer",
     *          "required"=false,
     *          "description"="Purchase amount. Min is 1 and max is 9999999."
     *      },
     *      {
     *          "name"="price",
     *          "dataType"="decimal",
     *          "required"=false,
     *          "format"="0.00",
     *          "description"="Purchase amount. Min is 0 and max is 9999999999."
     *      },
     *      {
     *          "name"="bought_at",
     *          "dataType"="date",
     *          "required"=false,
     *          "format"="Y-m-d",
     *          "description"="Purchase date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully updated purchase. Response will be like:",
     *          "{'message' => 'You successfully updated purchase.', 'data' => {'id' => ..., 'title' => ..., 'amount'
     *     => ..., 'price' => ..., 'bought_at' => ...}}"
     *      },
     *      400={
     *          "Validation fails. Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      },
     *      401="You are not authenticated.",
     *      404="Purchase not found.",
     *  }
     * )
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     *
     * @throws FormValidationException
     */
    public function patchAction($id, Request $request)
    {
        /** @var PurchaseHandler $handler */
        $handler = $this->container->get('budget_spending.purchase.handler');
        $purchase = $handler->patch($this->getOr404($id), $request->request->all());

        return View::create([
            'message' => 'You successfully updated purchase.',
            'data' => $purchase
        ], Codes::HTTP_OK);
    }

    /**
     * Delete purchase.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Delete purchase.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Purchase identity."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully deleted purchase. Response will be like:",
     *          "{'message' => 'You successfully deleted purchase.'}"
     *      },
     *      401="You are not authenticated.",
     *      404="Purchase not found.",
     *  }
     * )
     *
     * @param $id
     *
     * @return Response
     *
     * @throws FormValidationException
     */
    public function deleteAction($id)
    {
        /** @var PurchaseHandler $handler */
        $handler = $this->container->get('budget_spending.purchase.handler');
        $handler->delete($this->getOr404($id));

        return View::create(['message' => 'You successfully deleted purchase.'], Codes::HTTP_OK);
    }

    /**
     * Calculate sum of purchases for current month or all time.
     *
     * @ApiDoc(
     *  section="Purchase",
     *  resource = true,
     *  description="Calculate sum of purchases for current user. You could specify month via date query param.",
     *  filters={
     *      {
     *          "name"="string",
     *          "dataType"="date",
     *          "required"=false,
     *          "pattern"="Y-m",
     *          "description"="Month date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully deleted purchase. Response will be like:",
     *          "{'message' => 'You successfully deleted purchase.', 'data' => {'sum' => ...}}"
     *      },
     *      401="You are not authenticated.",
     *  }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sumAction(Request $request)
    {
        /** @var PurchaseHandler $handler */
        $handler = $this->container->get('budget_spending.purchase.handler');
        $date = $this->getGlobalDateFromRequest($request);
        $sum = $handler->getSum($date);

        return View::create(['data' => ['sum' => $sum]], Codes::HTTP_OK);
    }
}