<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Controller;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Gemeinsame TypoScript- und View-Konfiguration beider Plugin-Controller.
 *
 * Hintergrund: Beide Controller rendern über eine selbst erzeugte StandaloneView statt
 * über die vom ActionController bereitgestellte View. Dadurch greifen die
 * templateRootPaths/layoutRootPaths/partialRootPaths aus dem TypoScript nicht
 * automatisch — sie müssen von Hand gesetzt werden.
 *
 * Bis TYPO3 v11 wurde dafür die geschützte Methode ActionController::getViewProperty()
 * mitbenutzt. **Die gibt es in v12 nicht mehr** (sie war schon vorher als `@internal`
 * markiert). Sie ist hier deshalb nachgebildet.
 */
trait ViewConfigurationTrait
{
    /**
     * Liefert `plugin.tx_jwfeusermanager` als einfaches Array.
     */
    protected function getTyposcriptConfiguration(): array
    {
        $extensionName = $this->request->getControllerExtensionName();
        $conf = $this->getFullTyposcriptConfiguration();

        return $conf['plugin']['tx_' . strtolower($extensionName)] ?? [];
    }

    /**
     * Liefert das vollständige TypoScript der Seite als einfaches Array.
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
     * Überträgt die *RootPaths aus dem TypoScript auf die übergebene View.
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
     * Ersatz für die in TYPO3 v12 entfallene ActionController::getViewProperty().
     */
    protected function getViewProperty(array $configuration, string $setting): array
    {
        $value = $configuration['view'][$setting] ?? null;

        return is_array($value) ? $value : [];
    }
}
