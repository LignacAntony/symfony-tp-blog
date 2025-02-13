<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AppropriateUsername extends Constraint
{
    public string $message = 'Le nom d\'utilisateur "{{ value }}" n\'est pas approprié. Veuillez en choisir un autre.';
} 