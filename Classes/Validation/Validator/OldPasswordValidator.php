<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Validation\Validator;

use JwTue\FeUserManager\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Guards a password change on the edit form of an existing user.
 *
 * Attached to the new-password element. When a new password is submitted, the
 * user must also supply their current password in the accompanying "old password"
 * field, and it must match the stored hash — otherwise the password is not changed.
 *
 * The trigger ("a new password was entered") is the value of the element this
 * validator sits on, so no cross-field lookup is needed for it. The old-password
 * value lives in a sibling element, which is not available through the Extbase
 * validator API, so it is read from the current PSR-7 request. EXT:form namespaces
 * its submitted values under the form identifier, keyed by element identifier.
 */
class OldPasswordValidator extends AbstractValidator
{
    public function __construct(
        private readonly FrontendUser $user,
        private readonly string $formIdentifier,
        private readonly string $oldPasswordIdentifier,
        private readonly string $errorMessage,
    ) {
    }

    protected function isValid(mixed $value): void
    {
        // The AdvancedPassword element hands its value over either as the plain
        // password string or as an array with a "password" key, depending on the
        // TYPO3 version and processing stage — normalise both to a string.
        if (is_array($value)) {
            $value = $value['password'] ?? '';
        }

        // No new password entered -> no change requested -> nothing to guard.
        if (!is_string($value) || $value === '') {
            return;
        }

        $currentHash = (string)$this->user->getPassword();
        // A user without a usable stored hash cannot prove an old password; treat
        // any change attempt as unverifiable rather than silently allowing it.
        if ($currentHash === '') {
            $this->addError($this->errorMessage, 1753096001);
            return;
        }

        $oldPassword = $this->readSubmittedOldPassword();
        if ($oldPassword === '') {
            $this->addError($this->errorMessage, 1753096002);
            return;
        }

        $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)
            ->getDefaultHashInstance('FE');
        if (!$hashInstance->checkPassword($oldPassword, $currentHash)) {
            $this->addError($this->errorMessage, 1753096003);
        }
    }

    private function readSubmittedOldPassword(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request === null) {
            return '';
        }
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return '';
        }
        // EXT:form nests its values under the form identifier, which in turn sits
        // under the Extbase plugin namespace (tx_jwfeusermanager_edituser[<formId>][…]).
        // Accept both the namespaced and a hypothetical top-level layout.
        $candidates = [$body];
        foreach ($body as $sub) {
            if (is_array($sub)) {
                $candidates[] = $sub;
            }
        }
        foreach ($candidates as $candidate) {
            $formValues = $candidate[$this->formIdentifier] ?? null;
            if (is_array($formValues) && array_key_exists($this->oldPasswordIdentifier, $formValues)) {
                $value = $formValues[$this->oldPasswordIdentifier];
                return is_string($value) ? $value : '';
            }
        }
        return '';
    }
}
