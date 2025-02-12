<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Service\OpenAiService;

class AppropriateUsernameValidator extends ConstraintValidator
{
    public function __construct(
        private OpenAiService $openAiService
    ) {}

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->openAiService->validateUsername($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
} 