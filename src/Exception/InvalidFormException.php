<?php

declare(strict_types=1);

namespace App\Exception;

use App\Validator\Constraints\OverlappedOccurrence;
use Symfony\Component\Form\Form;

class InvalidFormException extends ApiException
{
    public const DEFAULT_MESSAGE = 'Submitted form did not pass validation.';

    protected Form $form;

    /**
     * @param string $message
     * @param int $code
     * @param array $data
     */
    public function __construct(string $message, int $code, array $data = [], $form)
    {
        parent::__construct($message, $code, $data);
        $this->form = $form;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getExceptionData(): array
    {
        return $this->getData();
    }
}
