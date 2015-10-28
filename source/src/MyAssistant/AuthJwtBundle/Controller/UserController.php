<?php namespace MyAssistant\AuthJwtBundle\Controller;


use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\CoreBundle\Exception\Api\FormValidationException;
use MyAssistant\CoreBundle\Exception\InvalidFormException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Post;

class UserController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  section="User",
     *  resource=false,
     *  description="Register new user.",
     *  parameters = {
     *      {
     *          "name"="username",
     *          "dataType"="string",
     *          "description"="User username",
     *          "required"="true"
     *      },
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "description"="User email",
     *          "required"="true"
     *      },
     *      {
     *          "name"="password_first",
     *          "dataType"="string",
     *          "description"="User password",
     *          "required"="true"
     *      },
     *      {
     *          "name"="password_second",
     *          "dataType"="string",
     *          "description"="User password confirmation",
     *          "required"="true"
     *      },
     *  },
     *  statusCodes = {
     *      201={
     *          "User successfully created. But you have to confirm user email. Confirmation url:",
     *          "/api/v1/email/{email}/confirm/{code}. Response will be like:",
     *          "{'message' => 'You successfully registered. In order to finish registration you have to confirm your",
     *          "email.', 'data' => {'id' => ..., 'username' => ..., 'email' => ..., 'timezone' => ...}}"
     *      },
     *      400={
     *          "Validation failed. Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      }
     *  }
     * )
     *
     * @Post("/users/registration")
     *
     * @param Request $request
     *
     * @throws FormValidationException
     *
     * @return Response
     */
    public function postRegisterAction(Request $request)
    {
        /** @var User $user */
        $user = $this->container->get('app_auth_jwt.registration.handler')->post(
            $request->request->all()
        );

        $message = \Swift_Message::newInstance();
        $message->setSubject('Email Confirmation')
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'MyAssistantAuthJwtBundle:User:registration.email.txt.twig',
                        [
                            'name' => $user->getUsername(),
                            'confirmationCode' => $user->getEmailConfirmation()->getConfirmationCode()
                        ]
                    )
                );
        $this->get('mailer')->send($message);

        return View::create([
            'data' => $user,
            'message' => 'You successfully registered. In order to finish registration you have to confirm your email.'
        ], Codes::HTTP_CREATED, [
            'Location' => $this->generateUrl(
                'get_user', ['id' => $user->getId()],
                true // absolute
            )
        ]);
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        return View::create($this->getOr404($id));
    }

    /**
     * @param $id
     *
     * @return User
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($purchase = $this->container->get('app_auth_jwt.user.handler')->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $purchase;
    }
}