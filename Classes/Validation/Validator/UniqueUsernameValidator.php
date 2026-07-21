<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Validation\Validator;

use JwTue\FeUserManager\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Checks when creating a new user whether the username is already taken.
 *
 * Replaces the anonymous validator class previously declared inline in the
 * EditFeUserController: its `isValid()` had the signature `public function isValid($value)`
 * and was therefore not compatible with TYPO3 v12 — there it is declared as
 * `abstract protected function isValid(mixed $value): void`.
 */
class UniqueUsernameValidator extends AbstractValidator
{
    public function __construct(private readonly FrontendUserRepository $userRepository)
    {
    }

    protected function isValid(mixed $value): void
    {
        if (!is_string($value) || $value === '') {
            return;
        }

        if ($this->userRepository->findForUsername($value)->count() > 0) {
            $this->addError('Benutzer mit diesem Namen existiert bereits', 1544001549);
        }
    }
}
