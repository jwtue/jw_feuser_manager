<?php
/**
 * Static verification of jw_feuser_manager against a real TYPO3 v12 installation.
 * Loads the TYPO3 autoloader, registers the extension via PSR-4 and checks via reflection
 * whether all referenced classes and the signatures we use actually exist.
 */

$typo3Root = getenv('TYPO3_ROOT') ?: '';
$extRoot   = dirname(__DIR__);

if ($typo3Root === '' || !is_file($typo3Root . '/vendor/autoload.php')) {
    fwrite(STDERR, "TYPO3_ROOT ist nicht gesetzt oder zeigt nicht auf eine Composer-basierte\n"
        . "TYPO3-v12-Installation (erwartet wird <TYPO3_ROOT>/vendor/autoload.php).\n\n"
        . "Aufruf:  TYPO3_ROOT=/pfad/zur/typo3-installation php Tests/verify-v12.php\n");
    exit(2);
}

$loader = require $typo3Root . '/vendor/autoload.php';
$loader->addPsr4('JwTue\\FeUserManager\\', $extRoot . '/Classes');

$fail = 0;
$warn = 0;
function ok(string $m): void    { echo "  ok    $m\n"; }
function bad(string $m): void   { global $fail; $fail++; echo "  FEHLER $m\n"; }
function note(string $m): void  { global $warn; $warn++; echo "  hinweis $m\n"; }

echo "== 1. Von der Extension referenzierte TYPO3-Klassen ==\n";
$referenced = [
    \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::class,
    \TYPO3\CMS\Extbase\Persistence\Repository::class,
    \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator::class,
    \TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator::class,
    \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator::class,
    \TYPO3\CMS\Extbase\Persistence\ObjectStorage::class,
    \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class,
    \TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
    \TYPO3\CMS\Extbase\Service\CacheService::class,
    \TYPO3\CMS\Core\Resource\ResourceFactory::class,
    \TYPO3\CMS\Core\Localization\LanguageServiceFactory::class,
    \TYPO3\CMS\Core\TypoScript\TypoScriptService::class,
    \TYPO3\CMS\Core\Database\ReferenceIndex::class,
    \TYPO3\CMS\Core\Page\PageRenderer::class,
    \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class,
    \TYPO3\CMS\Fluid\View\StandaloneView::class,
    \TYPO3\CMS\Form\Domain\Model\FormDefinition::class,
    \TYPO3\CMS\Form\Domain\Configuration\ConfigurationService::class,
    \TYPO3\CMS\Form\Domain\Finishers\ClosureFinisher::class,
];
foreach ($referenced as $c) {
    class_exists($c) || interface_exists($c) ? ok($c) : bad("$c existiert NICHT");
}

echo "\n== 2. Entfernte APIs dürfen nicht mehr vorkommen ==\n";
foreach ([
    'TYPO3\\CMS\\Extbase\\Object\\ObjectManager',
    'Causal\\ImageAutoresize\\Slots\\FileUpload',
] as $c) {
    class_exists($c) ? note("$c existiert (wird nur defensiv benutzt)") : ok("$c fehlt wie erwartet");
}
$ac = new ReflectionClass(\TYPO3\CMS\Extbase\Mvc\Controller\ActionController::class);
$ac->hasMethod('getViewProperty')
    ? note('ActionController::getViewProperty() existiert doch — Trait wäre entbehrlich')
    : ok('ActionController::getViewProperty() fehlt — Trait-Ersatz ist nötig');
$ac->hasProperty('extensionName')
    ? note('ActionController::$extensionName existiert doch')
    : ok('ActionController::$extensionName fehlt — Umstellung auf request war nötig');
$ac->hasProperty('contentObj')
    ? note('ActionController::$contentObj existiert doch')
    : ok('ActionController::$contentObj fehlt — eigene Deklaration ist nötig');

echo "\n== 3. Signaturen, auf die wir uns stützen ==\n";
$m = new ReflectionMethod(\TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator::class, 'isValid');
$sig = ($m->isProtected() ? 'protected' : 'public') . ' isValid(' .
    implode(', ', array_map(fn($p) => ($p->getType() ?? 'mixed') . ' $' . $p->getName(), $m->getParameters())) .
    '): ' . ($m->getReturnType() ?? 'void');
echo "  AbstractValidator::$sig\n";
$m->isProtected() ? ok('isValid ist protected — unsere Validator-Klasse passt') : bad('isValid ist nicht protected');
(new ReflectionClass(\TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator::class))->getConstructor()
    ? note('AbstractValidator hat einen Konstruktor — parent::__construct() wäre erlaubt')
    : ok('AbstractValidator hat keinen Konstruktor — parent::__construct() korrekt weggelassen');

$b = new ReflectionMethod(\TYPO3\CMS\Form\Domain\Model\FormDefinition::class, 'bind');
echo "  FormDefinition::bind() erwartet " . $b->getNumberOfParameters() . " Parameter\n";
$b->getNumberOfParameters() === 1 ? ok('bind($request) — einparametrig, wie umgestellt') : bad('bind() hat andere Parameterzahl');

foreach (['setRespectStoragePage', 'setIgnoreEnableFields', 'setIncludeDeleted'] as $method) {
    method_exists(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class, $method)
        ? ok("Typo3QuerySettings::$method() vorhanden")
        : bad("Typo3QuerySettings::$method() fehlt");
}
method_exists(\TYPO3\CMS\Core\Database\Query\QueryBuilder::class, 'executeStatement')
    ? ok('QueryBuilder::executeStatement() vorhanden')
    : bad('QueryBuilder::executeStatement() fehlt');

echo "\n== 4. Eigene Klassen: laden, Vererbung, abstrakte Methoden ==\n";
$own = [
    \JwTue\FeUserManager\Domain\Repository\FrontendUserRepository::class,
    \JwTue\FeUserManager\Domain\Repository\FrontendUserGroupRepository::class,
    \JwTue\FeUserManager\Domain\Repository\EditorFieldRepository::class,
    \JwTue\FeUserManager\Validation\Validator\UniqueUsernameValidator::class,
    \JwTue\FeUserManager\Domain\Model\FrontendUser::class,
    \JwTue\FeUserManager\Domain\Model\FrontendUserGroup::class,
    \JwTue\FeUserManager\Domain\Model\EditorField::class,
    \JwTue\FeUserManager\Controller\ShowFeUserController::class,
    \JwTue\FeUserManager\Controller\EditFeUserController::class,
];
foreach ($own as $c) {
    try {
        $r = new ReflectionClass($c);
        if ($r->isAbstract()) { bad("$c ist abstrakt — abstrakte Methode nicht implementiert?"); continue; }
        ok($r->getShortName() . ' lädt (extends ' . ($r->getParentClass() ? $r->getParentClass()->getShortName() : '—') . ')');
    } catch (Throwable $e) {
        bad("$c: " . $e->getMessage());
    }
}

echo "\n== 5. Konstruktor-Abhängigkeiten der Controller auflösbar? ==\n";
foreach ([\JwTue\FeUserManager\Controller\EditFeUserController::class,
          \JwTue\FeUserManager\Controller\ShowFeUserController::class] as $c) {
    $ctor = (new ReflectionClass($c))->getConstructor();
    if (!$ctor) { ok((new ReflectionClass($c))->getShortName() . ': kein eigener Konstruktor'); continue; }
    foreach ($ctor->getParameters() as $p) {
        $t = (string)$p->getType();
        class_exists($t) || interface_exists($t)
            ? ok((new ReflectionClass($c))->getShortName() . " braucht $t — auflösbar")
            : bad((new ReflectionClass($c))->getShortName() . " braucht $t — NICHT auflösbar");
    }
}

echo "\n== 6. Aufgerufene Methoden der eigenen Repositories ==\n";
method_exists(\JwTue\FeUserManager\Domain\Repository\FrontendUserRepository::class, 'findForUsername')
    ? ok('FrontendUserRepository::findForUsername() vorhanden')
    : bad('findForUsername() fehlt');
method_exists(\JwTue\FeUserManager\Domain\Repository\EditorFieldRepository::class, 'findForElement')
    ? ok('EditorFieldRepository::findForElement() vorhanden')
    : bad('findForElement() fehlt');

echo "\n== 7. Statisch aufgerufene eigene Klassen und mitgelieferte Bibliotheken ==\n";
class_exists(\JwTue\FeUserManager\ViewHelpers\Format\PhoneViewHelper::class)
    ? ok('ViewHelpers\Format\PhoneViewHelper auflösbar (Namespace plural)')
    : bad('ViewHelpers\Format\PhoneViewHelper nicht auflösbar');
method_exists(\JwTue\FeUserManager\ViewHelpers\Format\PhoneViewHelper::class, 'formatPhoneNumber')
    ? ok('PhoneViewHelper::formatPhoneNumber() vorhanden')
    : bad('formatPhoneNumber() fehlt');
is_file($extRoot . '/Resources/Private/Library/vcard/vCard.class.php')
    ? ok('Resources/Private/Library/vcard/vCard.class.php vorhanden (vCard-Export)')
    : bad('vCard.class.php fehlt — $download=="vcf" läuft in einen Fatal Error');

echo "\n== 8. Referenzen im Code gegen die Realität ==\n";
$sources = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extRoot . '/Classes')) as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $sources[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}
$missing = [];
foreach ($sources as $path => $code) {
    // Remove comments: class names in doc blocks are prose, not calls
    $code = preg_replace('#/\*.*?\*/#s', '', $code);
    $code = preg_replace('#//[^\n]*#', '', $code);

    // fully qualified class references of the form \Foo\Bar\Baz::
    preg_match_all('/\\\\((?:[A-Z][A-Za-z0-9_]*\\\\)+[A-Z][A-Za-z0-9_]*)::/', $code, $m);
    foreach (array_unique($m[1]) as $cls) {
        if (!class_exists($cls) && !interface_exists($cls)) {
            $missing[$cls][] = basename($path);
        }
    }
}
if ($missing === []) {
    ok('Alle vollqualifiziert referenzierten Klassen sind auflösbar');
} else {
    foreach ($missing as $cls => $files) {
        // classes from optional third-party extensions are to be expected here
        str_starts_with($cls, 'Causal\\')
            ? note("$cls fehlt (optionale Fremd-Extension, defensiv geprüft) — " . implode(', ', array_unique($files)))
            : bad("$cls nicht auflösbar — " . implode(', ', array_unique($files)));
    }
}

echo "\n---------------------------------------------\n";
echo $fail === 0 ? "ERGEBNIS: keine Fehler" : "ERGEBNIS: $fail Fehler";
echo ", $warn Hinweise\n";
exit($fail === 0 ? 0 : 1);
