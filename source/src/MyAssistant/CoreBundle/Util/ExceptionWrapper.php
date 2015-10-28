<?php  namespace MyAssistant\CoreBundle\Util;


use MyAssistant\CoreBundle\Exception\FlattenException;
use FOS\RestBundle\Util\ExceptionWrapper as BaseExceptionWrapper;

class ExceptionWrapper extends BaseExceptionWrapper
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var array
     */
    private $data;

    /**
     * Reset this property. We don't need this in our response.
     *
     * @var
     */
    private $code;

    public function __construct(array $data)
    {
        /** @var FlattenException $exception */
        $exception = $data['exception'];
        $data['message'] = $exception->getMessage();

        parent::__construct($data);

        if ($reason = $exception->getReason()) {
            $this->setReason($reason);
        }

        if ($additionalData = $exception->getAdditionalData()) {
            $this->setData($additionalData);
        }
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}