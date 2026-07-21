# Handoff: zwei Fixes aus dem FWTUE-v12-Staging-Test

> Erstellt 2026-07-21 beim Test des Edit-Formulars (`/intern/benutzerprofil`) auf dem
> FWTUE-v12-Staging (mit Claude Code). Diese Datei beschreibt zwei Befunde, die in
> **dieser** Extension behoben werden müssen. Der Analyse-Autor hat das JFM-Repo bewusst
> **nicht** selbst geändert (paralleles Refactoring läuft) — bitte hier umsetzen.

## Kurzüberblick

| # | Problem | Ursache | Status |
|---|---------|---------|--------|
| A | additional-Felder speichern nicht (Adresse geschäftl., Erläuterung, Arbeitsstelle-Radio, Fahrerlaubnis-MultiSelect) | DB-Spalten/EditorField-Config noch auf altem Präfix `tx_jwfrontendusermanager_*`, Modell/Schema auf `tx_jwfeusermanager_*` → Write über `method_exists`-Setter still verworfen | **Fix getestet** (reine DB-Migration, s.u.) |
| B | Radio-Buttons falsch angeordnet (Label links, Radio ganz rechts) | RadioButton-Container hat in EXT:form v12 nur `.input` (nicht `.input.radio`); CSS zielt auf nicht existente Klasse; zusätzlich Label = Geschwister des Inputs | **Fix hergeleitet** aus DOM, visuell zu verifizieren |
| — | Aufräumen: TEMP-Debug-Logging | Analyse-Commit `1423cc7` | **bitte reverten** |

---

## Aufräumen zuerst: Debug-Logging reverten

Für die Analyse wurde temporäres Logging in den `ClosureFinisher` von
`Classes/Controller/EditFeUserController.php` committet:

- **`1423cc7`** „TEMP: Save-Analyse-Logging im ClosureFinisher" → **bitte reverten**
  (schreibt `var/log/jwfeu_save.log`, reine Analyse).
- `701ccc2` „Revert .form-check-label-Reset …" kann bleiben (stellt nur einen
  verworfenen Zwischenstand zurück).

---

## Problem A — additional-Felder speichern nicht

### Ursache (durch Logging zweifelsfrei belegt)

Die Extension ist intern durchgängig auf **neue** Spaltennamen ausgelegt:
`ext_tables.sql` legt `tx_jwfeusermanager_additional_{text,boolean,bitfield}_1..5` an,
`Configuration/Extbase/Persistence/Classes.php` mappt `txJwfeusermanagerAdditionalText1 →
tx_jwfeusermanager_additional_text_1`, Setter heißen `setTxJwfeusermanagerAdditional…`.

In der **Datenbank** (aus der Alt-Extension `jw_frontend_user_manager` übernommen) gilt aber:

- `tx_jwfeusermanager_editorfield.db_field` zeigt noch auf `tx_jwfrontendusermanager_additional_*`
- die **Daten** liegen noch in den fe_users-Spalten `tx_jwfrontendusermanager_additional_*`
  (alle 15 Alt-Spalten existieren parallel zu den neuen)

Der Save-Pfad (`EditFeUserController::editAction()` → `ClosureFinisher`) schreibt via

```php
$kkey = $colsById[$key]->getUsableDbField();          // underscoredToLowerCamelCase(db_field)
$setFunctionName = 'set'.ucfirst($kkey);
if (method_exists($user, $setFunctionName)) { $user->$setFunctionName($value); }
```

Aus `db_field = tx_jwfrontendusermanager_additional_text_1` wird Setter
`setTxJwfrontendusermanagerAdditionalText1` → **existiert nicht** → Wert wird **still verworfen**.
Gelesen wird dagegen über rohes `SELECT *` (`FrontendUser::getFields()`), das die Alt-Spalten
erwischt — deshalb **Anzeige ok, Speichern nicht**. Standardfelder (email, address, city,
company …) haben echte Setter → speichern korrekt.

Log-Beleg (vor/nach der Migration, gleiche Felder):

```
VORHER: db_field=tx_jwfrontendusermanager_additional_text_1  setter=setTxJwfrontendusermanagerAdditionalText1  exists=NO
NACHHER: db_field=tx_jwfeusermanager_additional_text_1        setter=setTxJwfeusermanagerAdditionalText1        exists=YES  value=Am Campus 1 …
```

### Fix = reine DB-Migration (kein PHP-Bug, kein Code-Change am Save-Pfad nötig)

Nur das Mittelsegment unterscheidet sich (`frontendusermanager` → `feusermanager`),
Ziffern-/Unterstrich-Schema ist identisch. Zwei Schritte:

1. Daten aus den Alt-Spalten in die neuen Spalten kopieren.
2. `tx_jwfeusermanager_editorfield.db_field` auf die neuen Namen umschreiben.

**Auf dem Staging bereits als SQL ausgeführt und verifiziert** (Reads + Writes laufen danach
über die neuen Spalten, `exists=YES`, Werte persistieren). Getestetes SQL:

```sql
-- 1) Daten Alt -> Neu (alle 15 additional-Spalten existieren beidseitig):
UPDATE fe_users SET
  tx_jwfeusermanager_additional_text_1     = tx_jwfrontendusermanager_additional_text_1,
  tx_jwfeusermanager_additional_text_2     = tx_jwfrontendusermanager_additional_text_2,
  tx_jwfeusermanager_additional_text_3     = tx_jwfrontendusermanager_additional_text_3,
  tx_jwfeusermanager_additional_text_4     = tx_jwfrontendusermanager_additional_text_4,
  tx_jwfeusermanager_additional_text_5     = tx_jwfrontendusermanager_additional_text_5,
  tx_jwfeusermanager_additional_boolean_1  = tx_jwfrontendusermanager_additional_boolean_1,
  tx_jwfeusermanager_additional_boolean_2  = tx_jwfrontendusermanager_additional_boolean_2,
  tx_jwfeusermanager_additional_boolean_3  = tx_jwfrontendusermanager_additional_boolean_3,
  tx_jwfeusermanager_additional_boolean_4  = tx_jwfrontendusermanager_additional_boolean_4,
  tx_jwfeusermanager_additional_boolean_5  = tx_jwfrontendusermanager_additional_boolean_5,
  tx_jwfeusermanager_additional_bitfield_1 = tx_jwfrontendusermanager_additional_bitfield_1,
  tx_jwfeusermanager_additional_bitfield_2 = tx_jwfrontendusermanager_additional_bitfield_2,
  tx_jwfeusermanager_additional_bitfield_3 = tx_jwfrontendusermanager_additional_bitfield_3,
  tx_jwfeusermanager_additional_bitfield_4 = tx_jwfrontendusermanager_additional_bitfield_4,
  tx_jwfeusermanager_additional_bitfield_5 = tx_jwfrontendusermanager_additional_bitfield_5;

-- 2) EditorField-Config auf neue Spaltennamen:
UPDATE tx_jwfeusermanager_editorfield
SET db_field = REPLACE(db_field, 'tx_jwfrontendusermanager_', 'tx_jwfeusermanager_')
WHERE db_field LIKE 'tx_jwfrontendusermanager\_%';
```

> Hinweis: Falls `SHOW COLUMNS FROM fe_users LIKE 'tx_jwfrontendusermanager_%'` zusätzlich
> `…_newsletter_subscribed` / `…_lastupdated` in der Alt-Schreibweise zeigt, analog mitkopieren.

### Empfohlen: als Upgrade-Wizard (reproduzierbar für den Produktions-Cutover)

Passt ins Muster von `Classes/Updates/LegacyFeUserPluginUpgrade.php`. Der `CASE`-Guard macht
die Datenkopie wiederholbar (überschreibt bereits migrierte Werte nicht):

```php
<?php
declare(strict_types=1);

namespace JwTue\FeUserManager\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\{ChattyInterface, DatabaseUpdatedPrerequisite, RepeatableInterface, UpgradeWizardInterface};

#[UpgradeWizard('jwFeUserManager_additionalFieldsColumnRename')]
final class AdditionalFieldsColumnRenameUpgrade implements UpgradeWizardInterface, ChattyInterface, RepeatableInterface
{
    private const OLD = 'tx_jwfrontendusermanager_';
    private const NEW = 'tx_jwfeusermanager_';
    private const SUFFIXES = ['additional_text_%d', 'additional_boolean_%d', 'additional_bitfield_%d'];

    private OutputInterface $output;
    public function __construct(private readonly ConnectionPool $connectionPool) {}
    public function setOutput(OutputInterface $o): void { $this->output = $o; }
    public function getTitle(): string { return 'jw_feuser_manager: migrate additional_* columns to new naming'; }
    public function getDescription(): string {
        return 'Copies fe_users data from predecessor columns tx_jwfrontendusermanager_additional_* '
             . 'to tx_jwfeusermanager_additional_* and rewrites tx_jwfeusermanager_editorfield.db_field.';
    }
    public function getPrerequisites(): array { return [DatabaseUpdatedPrerequisite::class]; }

    public function updateNecessary(): bool {
        $qb = $this->connectionPool->getQueryBuilderForTable('tx_jwfeusermanager_editorfield');
        $qb->getRestrictions()->removeAll();
        return (int)$qb->count('uid')->from('tx_jwfeusermanager_editorfield')
            ->where($qb->expr()->like('db_field', $qb->createNamedParameter(self::OLD . '%')))
            ->executeQuery()->fetchOne() > 0;
    }

    public function executeUpdate(): bool {
        $conn = $this->connectionPool->getConnectionForTable('fe_users');
        $cols = array_map('strtolower', array_keys($conn->createSchemaManager()->listTableColumns('fe_users')));

        // 1) Daten kopieren – nur existente Spaltenpaare, Guard gegen Clobber (repeatable-sicher)
        $sets = [];
        foreach (self::SUFFIXES as $tpl) {
            for ($i = 1; $i <= 5; $i++) {
                $old = self::OLD . sprintf($tpl, $i);
                $new = self::NEW . sprintf($tpl, $i);
                if (in_array(strtolower($old), $cols, true) && in_array(strtolower($new), $cols, true)) {
                    $default = str_contains($tpl, 'text') ? "''" : '0';
                    $sets[] = "`$new` = CASE WHEN `$new` = $default THEN `$old` ELSE `$new` END";
                }
            }
        }
        if ($sets) {
            $affected = $conn->executeStatement('UPDATE fe_users SET ' . implode(', ', $sets));
            $this->output->writeln(sprintf('  fe_users: %d row(s) touched.', $affected));
        }

        // 2) editorfield.db_field umschreiben
        $ef = $this->connectionPool->getConnectionForTable('tx_jwfeusermanager_editorfield');
        $rows = $ef->executeStatement(
            'UPDATE tx_jwfeusermanager_editorfield SET db_field = REPLACE(db_field, ?, ?) WHERE db_field LIKE ?',
            [self::OLD, self::NEW, self::OLD . '%']
        );
        $this->output->writeln(sprintf('  editorfield.db_field: %d record(s) rewritten.', $rows));
        return true;
    }
}
```

Registrierung läuft automatisch über das `#[UpgradeWizard]`-Attribut + `public: true` für
`Updates/` (steht bereits in `Configuration/Services.yaml`). Ausführen via Install-Tool
„Upgrade Wizards" bzw. `vendor/bin/typo3 upgrade:run`.

---

## Problem B — Radio-Buttons falsch angeordnet

### Symptom
Beim Radio-Feld (`MODE_DB_OPTIONS`, z. B. „Kann von Arbeitsstelle ausrücken") steht das
Options-Label links und der Radio-Button ganz rechts; „Teilweise" bricht zusätzlich um.
Die **MultiCheckbox** (Fahrerlaubnis) sitzt dagegen korrekt (☑ links, Label rechts).

### Ursache (aus den EXT:form-v12-Partials + FormSetup verifiziert)

DOM-Unterschied laut EXT:form-Default-Partials (v12):

```html
<!-- MultiCheckbox: Input liegt IM Label -->
<div class="input checkbox"> …
  <div class="form-check">
    <label class="form-check-label"><input type=checkbox> <span>Klasse B</span></label>
  </div>
</div>

<!-- RadioButton: Input ist GESCHWISTER VOR dem Label -->
<label class="form-label">Kann von Arbeitsstelle ausrücken:</label>
<div class="input"> …                          <!-- nur .input, KEIN .radio -->
  <div class="inputs-list"><div class="form-group">
    <div class="form-check">
      <input class="… form-check-input" type=radio>
      <label class="form-check-label"><span>Ja</span></label>
    </div>
  </div></div>
</div>
```

Zwei Faktoren:

1. **Container-Klasse:** EXT:form v12 gibt der MultiCheckbox `containerClassAttribute: 'input checkbox'`
   (→ `.input.checkbox` greift), dem **RadioButton nur `input`** (RadioButton.yaml). Das bestehende
   CSS zielt aber auf `.input .radio` (mit Leerzeichen = Nachfahre) bzw. meint `.input.radio` — diese
   Klasse existiert am Radio-Container **nicht**. Also greift **keine** Radio-Regel; `.form-check-label`
   erbt die generische Regel `.tx-jwfeusermanager-edituser label { width:160px; float:left; display:block }`.
2. **Struktur:** Da der Radio-Input ein **Geschwister vor** dem Label ist (nicht umschlossen), reicht
   ein reiner `float:none; width:auto`-Reset nicht — bei `display:block` rutscht das Label unter den
   Input (das war der verschlimmernde Effekt eines isolierten `.form-check-label`-Resets). Das Label
   braucht zusätzlich **`display:inline`**.

### Fix (zwei kleine Änderungen)

**1) Controller** `Classes/Controller/EditFeUserController.php`, im `MODE_DB_OPTIONS`-Zweig direkt
nach `createElement(…, 'RadioButton')` die Container-Klasse setzen — analog zu ImageCrop
(`"input imagecrop"`) und dem Checkbox-Default (`"input checkbox"`):

```php
} else if ($col->getDbMode() == EditorField::MODE_DB_OPTIONS) {
    $el = $page1->createElement("editorfield_".$col->getUid(), 'RadioButton');
    $el->setProperty("containerClassAttribute", "input radio");   // <-- NEU
    …
```

(Alternativ deklarativ in `Configuration/Yaml/FormSetup.yaml` für `RadioButton`
`containerClassAttribute: 'input radio'` setzen — konsistent zum EXT:form-Checkbox-Default.)

**2) CSS** `Resources/Public/Css/jw_feuser_manager.css`:

- Den bestehenden **Space-Bug** beheben: `.input .radio` → `.input.radio` (in der Label-Reset-Regel
  und in der Indent-Regel, aktuell ~Z. 22 und ~Z. 27).
- Für das Radio-Label wegen der Geschwister-Struktur zusätzlich `display:inline` ergänzen. Sauberer,
  selbstständiger Block:

```css
/* Radio-Gruppe (v12 EXT:form: Container .input.radio, Items .form-check mit Input+Label als Geschwister) */
.tx-jwfeusermanager-edituser .input.radio {
    width: auto;
    margin-left: 170px;          /* Gruppe unter dem Feld-Label einrücken – wie .input.checkbox */
}
.tx-jwfeusermanager-edituser .input.radio .form-check-label {
    float: none;
    width: auto;
    display: inline;             /* neben dem Radio halten (Input ist Geschwister, nicht umschlossen) */
}
```

- Im Mobile-Block (`@media screen and (max-width: 620px)`) `.input.radio` neben `.input.checkbox`
  aufnehmen, damit dort `margin-left: 0` gilt.

Erwartetes Ergebnis: vertikale Liste „○ Ja / ● Nein / ○ Teilweise (bitte erläutern)", Radio links,
Label direkt daneben, Gruppe bündig zu den übrigen Feld-Eingaben — wie in v11.

> Hinweis: aus DOM/Partials hergeleitet, nicht visuell auf Staging getestet (JFM-Repo wurde bewusst
> nicht angefasst). Nach Umsetzung bitte einmal `/intern/benutzerprofil` prüfen (Hard-Reload, CSS ist
> statisch/gecacht).

---

## Problem C (Nebenbefund, niedrige Prio) — Listen-Text-Spalten ohne Multiline-Formatierung

In `Resources/Private/Templates/ShowFeUser/List.html` heißen die `<f:switch expression="{column}">`-Cases
für die Text-Spalten `txJwfrontenduserAdditionalText1..5` (Z. ~203–217) — **ohne** „manager", im
Gegensatz zu allen anderen Cases (`txJwfeusermanagerAdditional…`, `txJwfeusermanagerLastupdated` usw.).
Der tatsächliche Runtime-`{column}`-Schlüssel ist `txJwfeusermanagerAdditionalText1` (camelCase der
db_field-Spalte). Die Text-Cases matchen also **nie** → Text fällt in `<f:defaultCase>` (Default- statt
`Multiline`-Partial → Zeilenumbrüche gehen in der Listenansicht verloren).

**Fix:** in den fünf Cases `txJwfrontenduserAdditionalText` → `txJwfeusermanagerAdditionalText`.
Vorbestehend (matchte auch vor der Spalten-Migration nicht), daher unabhängig von Problem A.
