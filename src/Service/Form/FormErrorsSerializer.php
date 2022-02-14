<?php

namespace App\Service\Form;

use Symfony\Component\Form\FormInterface;

class FormErrorsSerializer
{
    /**
     * @var array
     */
    protected array $errors;

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function serialize(FormInterface $form): array
    {
        $this->errors = $this->serializeErrors($form);

        return $this->errors;
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    protected function serializeErrors(FormInterface $form): array
    {
        $formErrors = [];
        foreach ($form->getErrors() as $error) {
            $formErrors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && $childErrors = $this->serializeErrors($childForm)) {
                $formErrors[$childForm->getName()] = $childErrors;
            }
        }

        return $formErrors;
    }
}
