<?php namespace MyAssistant\CoreBundle\Exception;


use MyAssistant\CoreBundle\Exception\Api\ApiExceptionInterface;
use Symfony\Component\Debug\Exception\FlattenException as BaseFlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FlattenException extends BaseFlattenException
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var array
     */
    private $additionalData;

    public static function create(\Exception $exception, $statusCode = null, array $headers = [])
    {
        $e = new static();
        $e->setMessage($exception->getMessage());
        $e->setCode($exception->getCode());

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = array_merge($headers, $exception->getHeaders());
        }

        if (null === $statusCode) {
            $statusCode = 500;
        }

        $e->setStatusCode($statusCode);
        $e->setHeaders($headers);
        $e->setTraceFromException($exception);
        $e->setClass(get_class($exception));
        $e->setFile($exception->getFile());
        $e->setLine($exception->getLine());
        if ($exception->getPrevious()) {
            $e->setPrevious(static::create($exception->getPrevious()));
        }

        if ($exception instanceof ApiExceptionInterface) {
            $e->setReason($exception->getReason());
            $e->setAdditionalData($exception->getAdditionalData());
        }

        return $e;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param array $additionalData
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }
}