<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Controller;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Shared TypoScript and view configuration for both plugin controllers.
 *
 * Background: both controllers render via a self-created StandaloneView instead of the
 * view provided by the ActionController. As a result the
 * templateRootPaths/layoutRootPaths/partialRootPaths from the TypoScript do not apply
 * automatically — they have to be set by hand.
 *
 * Up to TYPO3 v11 the protected method ActionController::getViewProperty() was used for
 * this. **It no longer exists in v12** (it was already marked as `@internal` before).
 * It is therefore reimplemented here.
 */
trait ViewConfigurationTrait
{
    /**
     * Returns `plugin.tx_jwfeusermanager` as a plain array.
     */
    protected function getTyposcriptConfiguration(): array
    {
        $extensionName = $this->request->getControllerExtensionName();
        $conf = $this->getFullTyposcriptConfiguration();

        return $conf['plugin']['tx_' . strtolower($extensionName)] ?? [];
    }

    /**
     * Returns the complete TypoScript of the page as a plain array.
     */
    protected function getFullTyposcriptConfiguration(): array
    {
        $conf = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            $this->request->getControllerExtensionName()
        );

        return (new TypoScriptService())->convertTypoScriptArrayToPlainArray($conf);
    }

    /**
     * Transfers the *RootPaths from the TypoScript to the given view.
     */
    protected function setViewConfiguration($view): void
    {
        $configuration = $this->getTyposcriptConfiguration();

        foreach (['templateRootPaths', 'layoutRootPaths', 'partialRootPaths'] as $setting) {
            $setter = 'set' . ucfirst($setting);
            if (!method_exists($view, $setter)) {
                continue;
            }

            $paths = $this->getViewProperty($configuration, $setting);
            if ($paths !== []) {
                $view->{$setter}($paths);
            }
        }
    }

    /**
     * Replacement for ActionController::getViewProperty(), which was removed in TYPO3 v12.
     */
    protected function getViewProperty(array $configuration, string $setting): array
    {
        $value = $configuration['view'][$setting] ?? null;

        return is_array($value) ? $value : [];
    }
}
