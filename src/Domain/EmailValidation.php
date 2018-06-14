<?php

namespace HexagonalPlayground\Domain;

trait EmailValidation
{
    /**
     * @param string $email
     * @throws DomainException
     */
    protected function validateEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Invalid email syntax');
        }
    }
}