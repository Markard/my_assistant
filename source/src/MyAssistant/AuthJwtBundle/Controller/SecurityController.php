<?php namespace MyAssistant\AuthJwtBundle\Controller;


use MyAssistant\AuthJwtBundle\Entity\EmailConfirmation;
use MyAssistant\AuthJwtBundle\Exception\Api\ResendTimeoutNotExpiredException;
use MyAssistant\AuthJwtBundle\Handler\RegistrationHandler;
use MyAssistant\CoreBundle\Exception\Api\ManuallyFormException;
use MyAssistant\CoreBundle\Exception\Api\NotFoundException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class SecurityController extends FOSRestController
{
    /**
     * Authentication method. You will get jwt token if credentials are valid.
     *
     * @ApiDoc(
     *  section="Authentication",
     *  resource=false,
     *  description="Authenticate user. Return jwt token on success.",
     *  parameters = {
     *      {
     *          "name"="username",
     *          "dataType"="string",
     *          "description"="User username or email.",
     *          "required"=true
     *      },
     *      {
     *          "name"="password",
     *          "dataType"="string",
     *          "description"="User password.",
     *          "required"=true
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "User successfully authenticated. Response will be like:",
     *          "{'token' => 'your_token'}"
     *      },
     *      400={
     *          "Username or password is invalid then response will be:",
     *          "{'reason' => 'invalidParams', 'message' => '', 'form' => {'global' => 'Bad credentials.'}}.",
     *          "Or email doesn't confirmed then reason response will be:",
     *          "{'reason' => 'emailNotConfirmed', 'message' => '...', 'data' => {'email' => user_email}}."
     *      }
     *  }
     * )
     */
    public function getTokenAction()
    {
        // The security layer will intercept this request
        return new Response('', 401);
    }

    /**
     * Confirm user email and finish registration.
     *
     * @ApiDoc(
     *  section="Authentication",
     *  resource=false,
     *  description="Confirm user email with confirmation code which was sent to user email after registration.",
     *  requirements = {
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "description"="User email.",
     *      },
     *      {
     *          "name"="code",
     *          "dataType"="string",
     *          "description"="Email confirmation code.",
     *      },
     *  },
     *  statusCodes = {
     *      200={
     *          "Confirm successful. Response contains jwt token and will be like:",
     *          "{'message' => 'Email successfully confirmed.', 'data' => {'token' => 'your_token'}}.",
     *      },
     *      400={
     *          "Validation fails or confirmation code is invalid. Response will be like:",
     *          "{'message' => 'Invalid submitted data', 'reason' => 'formValidationFailed', ",
     *          "'data' => {'global' => [...], 'fields' => [...]}}"
     *      }
     *  }
     * )
     *
     * @param $email
     * @param $code
     *
     * @return Response
     *
     * @throws ManuallyFormException
     */
    public function confirmRegistrationAction($email, $code)
    {
        $repository = $this->getDoctrine()->getRepository('MyAssistant\AuthJwtBundle\Entity\EmailConfirmation');

        /** @var EmailConfirmation $emailConfirmation */
        $emailConfirmation = $repository->findOneBy(['email' => $email, 'confirmationCode' => $code]);
        if (!$emailConfirmation) {
            throw new ManuallyFormException([], [
                'confirmation_code' => 'Confirmation code is invalid. You can try to resend email once more.'
            ]);
        }

        $expirationHoursDelta = $this->getParameter('confirmation_code_expiration_period_in_hours');
        if ($emailConfirmation->isCodeExpired($expirationHoursDelta)) {
            throw new ManuallyFormException([], [
                'confirmation_code' => 'Confirmation code is expired. You can try to resend email once more.'
            ]);
        }

        /** @var RegistrationHandler $handler */
        $handler = $this->get('app_auth_jwt.registration.handler');
        $handler->delete($emailConfirmation);

        $userRepository = $this->getDoctrine()->getRepository('MyAssistant\AuthJwtBundle\Entity\User');
        $user = $userRepository->findOneBy(['email' => $emailConfirmation->getEmail()]);
        $jwt = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);

        return View::create([
            'data' => [
                'token' => $jwt,
            ],
            'message' => 'Email successfully confirmed.'
        ]);
    }

    /**
     * Resend confirmation code to user email if time delta is valid.
     *
     * @ApiDoc(
     *  section="Authentication",
     *  resource=false,
     *  description="Resend email confirmation code with to user email. You can use this only once in 10 minutes",
     *  requirements = {
     *      {
     *          "name"="email",
     *          "dataType"="string",
     *          "description"="User email.",
     *      }
     *  },
     *  statusCodes = {
     *      200={
     *          "Email was sent successfully. Response will be like:",
     *          "{'message' => 'Confirmation code was successfully sent to your email.'}"
     *      },
     *      404={
     *          "Email not found. Response will be like:",
     *          "{'message' => 'Email not found.', 'reason' => 'notFound'}",
     *          "or",
     *          "{'message' => 'You already resend confirmation code. Please wait a little.', 'reason' => 'resendTimeout' }"
     *      }
     *  }
     * )
     *
     * @param Request $request
     * @param email
     *
     * @return Response
     *
     * @throws NotFoundException
     * @throws ResendTimeoutNotExpiredException
     */
    public function resendConfirmationCodeAction(Request $request, $email)
    {
        $repository = $this->getDoctrine()->getRepository('MyAssistant\AuthJwtBundle\Entity\EmailConfirmation');
        /** @var EmailConfirmation $emailConfirmation */
        $emailConfirmation = $repository->findOneBy(['email' => $email]);

        if (!$emailConfirmation) {
            throw new NotFoundException('Email not found.');
        }

        $resendTimeout = $this->getParameter('confirmation_code_resend_timeout_in_minutes');
        if (!$emailConfirmation->isResendTimeoutExpired($resendTimeout)) {
            throw new ResendTimeoutNotExpiredException('You already resend confirmation code. Please wait a little.');
        }

        $emailConfirmation->refreshCode();
        $this->getDoctrine()->getManager()->flush();

        $user = $emailConfirmation->getUser();
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

        return View::create(['message' => 'Confirmation code was successfully sent to your email.']);
    }
}
