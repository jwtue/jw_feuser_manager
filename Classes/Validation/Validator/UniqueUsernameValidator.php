<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Validation\Validator;

use JwTue\FeUserManager\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Prüft beim Anlegen eines neuen Benutzers, ob der Benutzername bereits vergeben ist.
 *
 * Ersetzt die zuvor inline im EditFeUserController deklarierte anonyme Validator-Klasse:
 * deren `isValid()` hatte die Signatur `public function isValid($value)` und war damit
 * nicht mit TYPO3 v12 kompatibel — dort ist sie als
 * `abstract protected function isValid(mixed $value): void` deklariert.
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
