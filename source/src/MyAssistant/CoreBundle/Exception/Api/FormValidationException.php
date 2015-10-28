<?php namespace MyAssistant\CoreBundle\Exception\Api;


use Doctrine\Common\Util\Inflector;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\FormInterface;

class FormValidationException extends ApiException
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct(Form $form, $message = "", $code = 0, Exception $previous = null)
    {
        $this->form = $form;
        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'formValidationFailed';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalData()
    {
        $errors = [];
        $errors['global'] = [];
        $errors['fields'] = [];

        foreach ($this->form->getErrors() as $formError) {
            $errors['global'][] = $formError->getMessage();
        }

        $errors['fields'] = $this->serialize($this->form);
        $errors['fields'] = $this->arrayFlatten($errors['fields'], '_');

        return $errors;
    }

    private function serialize(Form $form)
    {
        $localErrors = [];
        foreach ($form as $key => $child) {

            foreach ($child->getErrors() as $error) {
                $localErrors[Inflector::tableize($key)] = $error;
            }

            if (count($child) > 0) {
                $localErrors[Inflector::tableize($key)] = $this->serialize($child);
            }
        }

        return $localErrors;
    }

    private function arrayFlatten($array, $separator = '_', $flattened_key = null)
    {
        $flattenedArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattenedArray = array_merge($flattenedArray,
                    $this->arrayFlatten($value, $separator,
                        ($flattened_key ? $flattened_key . $separator : '') . $key)
                );
            } else {
                $flattenedArray[($flattened_key ? $flattened_key . $separator : '') . $key] = $value;
            }
        }

        return $flattenedArray;
    }
}