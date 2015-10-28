<?php namespace MyAssistant\CoreBundle\EventListener;


use MyAssistant\CoreBundle\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as HttpKernelExceptionListener;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Class ExceptionListener.
 * We implement this listener only because we have to have our custom FlattenException with our custom
 * "reason" field and "data" fields for our api exceptions.
 */
class ExceptionListener extends HttpKernelExceptionListener
{
    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = [
            '_controller' => $this->controller,
            'exception' => FlattenException::create($exception),
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
            // keep for BC -- as $format can be an argument of the controller callable
            // see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
            // @deprecated since version 2.4, to be removed in 3.0
            'format' => $request->getRequestFormat(),
        ];
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        return $request;
    }
}