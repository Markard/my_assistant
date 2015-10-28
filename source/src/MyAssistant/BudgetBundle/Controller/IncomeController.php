<?php namespace MyAssistant\BudgetBundle\Controller;

use Carbon\Carbon;
use Doctrine\Common\Inflector\Inflector;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\BudgetBundle\Entity\Income;
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

class IncomeController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Return list of user incomes.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Return list of user incomes.",
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
     *          "default"="date",
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
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully got list of data. Response will be like:",
     *          "{'total_count' => ...,'num_items_per_page' => ..., 'page' => ..., 'items' => [..., {",
     *          "'id' => ..., 'title' => ..., 'price' => ..., 'date' => ...",
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

        $sort = Inflector::camelize($request->query->get('sort', 'date'));
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
        $handler = $this->container->get('budget_spending.income.handler');
        $incomes = $handler->all($limit, $page, $sort, $orderBy, $filters);

        return View::create($incomes);
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
     * Return information about special income.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Return information about special income.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Income identity."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully got information about special income. Response will be like:",
     *          "{'id' => ..., 'title' => ..., 'price' => ..., 'date' => ...}"
     *      },
     *      401="You are not authenticated.",
     *      404="Income not found.",
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
     * @return Income
     *
     * @throws NotFoundException
     */
    protected function getOr404($id)
    {
        if (!($income = $this->container->get('budget_spending.income.handler')->get($id))) {
            throw new NotFoundException("Income with id: $id  was not found.");
        }

        return $income;
    }

    /**
     * Create income for current user.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Create income for current user.",
     *  parameters={
     *      {
     *          "name"="title",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Income title. Min is 1 symbol and max is 255."
     *      },
     *      {
     *          "name"="price",
     *          "dataType"="decimal",
     *          "required"=true,
     *          "format"="0.00",
     *          "description"="Income amount. Min is 0 and max is 9999999999."
     *      },
     *      {
     *          "name"="date",
     *          "dataType"="date",
     *          "required"=true,
     *          "format"="Y-m-d",
     *          "description"="Income date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully create income. Response will be like:",
     *          "{'message' => 'You successfully created income.', 'data' => {'id' => ..., 'title' => ..., 'price' =>
     *     ...,
     * 'date' => ...}}"
     *      },
     *      400={
     *          "Validation fails or you exceed your month limit. User can't create more than 1000 incomes per month.
     *     Response will be like:",
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
        if ($user->getIncomesPerMonth() >= $this->getParameter('purchases_limit_per_day')) {
            throw new CustomApiException("You exceed your month limit. You can't create income in this month.");
        }

        /** @var Income $income */
        $income = $this->container->get('budget_spending.income.handler')->post(
            $request->request->all()
        );

        return View::create([
            'message' => 'You successfully created income.',
            'data' => $income
        ], Codes::HTTP_CREATED, [
            'Location' => $this->generateUrl(
                'get_income', ['id' => $income->getId()],
                true // absolute
            )
        ]);
    }

    /**
     * Update all fields in record.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Update all fields in record.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Income identity."
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
     *          "name"="price",
     *          "dataType"="decimal",
     *          "required"=true,
     *          "format"="0.00",
     *          "description"="Purchase amount. Min is 0 and max is 9999999999."
     *      },
     *      {
     *          "name"="date",
     *          "dataType"="date",
     *          "required"=true,
     *          "format"="Y-m-d",
     *          "description"="Purchase date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully updated item. Response will be like:",
     *          "{'message' => 'You successfully updated purchase.', 'data' => {'id' => ..., 'title' => ..., 'price' =>
     *     ..., 'date' => ...}}"
     *      },
     *      400={
     *          "Validation fails. Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      },
     *      401="You are not authenticated.",
     *      404="Item not found.",
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
        $handler = $this->container->get('budget_spending.income.handler');
        $income = $handler->put($this->getOr404($id), $request->request->all());

        return View::create([
            'message' => 'You successfully updated income.',
            'data' => $income
        ], Codes::HTTP_OK);
    }

    /**
     * Update some fields in record.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Update some fields in record.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Income identity."
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
     *          "name"="price",
     *          "dataType"="decimal",
     *          "required"=false,
     *          "format"="0.00",
     *          "description"="Purchase amount. Min is 0 and max is 9999999999."
     *      },
     *      {
     *          "name"="date",
     *          "dataType"="date",
     *          "required"=false,
     *          "format"="Y-m-d",
     *          "description"="Income date."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully updated income. Response will be like:",
     *          "{'message' => 'You successfully updated income.', 'data' => {'id' => ..., 'title' => ..., 'price' =>
     *     ..., 'date' => ...}}"
     *      },
     *      400={
     *          "Validation fails. Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      },
     *      401="You are not authenticated.",
     *      404="Income not found.",
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
        $handler = $this->container->get('budget_spending.income.handler');
        $income = $handler->patch($this->getOr404($id), $request->request->all());

        return View::create([
            'message' => 'You successfully updated income.',
            'data' => $income
        ], Codes::HTTP_OK);
    }

    /**
     * Delete income.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Delete income.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Income identity."
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "You successfully deleted income. Response will be like:",
     *          "{'message' => 'You successfully deleted income.'}"
     *      },
     *      401="You are not authenticated.",
     *      404="Income not found.",
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
        $handler = $this->container->get('budget_spending.income.handler');
        $handler->delete($this->getOr404($id));

        return View::create(['message' => 'You successfully deleted income.'], Codes::HTTP_OK);
    }

    /**
     * Calculate sum of incomes for current month or all time.
     *
     * @ApiDoc(
     *  section="Income",
     *  resource = true,
     *  description="Calculate sum of incomes for current user. You could specify month via date query param.",
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
     *          "You successfully deleted income. Response will be like:",
     *          "{'message' => 'You successfully deleted income.', 'data' => {'sum' => ...}}"
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
        $handler = $this->container->get('budget_spending.income.handler');
        $date = $this->getGlobalDateFromRequest($request);
        $sum = $handler->getSum($date);

        return View::create(['data' => ['sum' => $sum]], Codes::HTTP_OK);
    }
}