# jw_feuser_manager

TYPO3-Extension zur Verwaltung von **Frontend-Benutzern im Frontend**: Mitgliederliste,
Detailansicht, Selbstpflege der eigenen Daten sowie Export als PDF, CSV und vCard.

Nachfolger der Extension `jw_frontendusermanager` (TYPO3 v8–v11), die ihrerseits
`datamints_feuser` abgelöst hat.

| | |
|---|---|
| Extension-Key | `jw_feuser_manager` |
| Composer-Paket | `jwtue/jw_feuser_manager` |
| PHP-Namespace | `JwTue\FeUserManager\` |
| TYPO3 | 12.4 |
| PHP | ≥ 8.0 |
| Lizenz | MIT |

## Installation

```bash
composer require jwtue/jw_feuser_manager:dev-main
```

Das Paket liegt nicht auf Packagist. Die einbindende TYPO3-Installation braucht deshalb
einen VCS-Eintrag in ihrer `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/jwtue/jw_feuser_manager.git" }
]
```

Nach der Installation im Install-Tool den Datenbank-Abgleich ausführen — die Extension
erweitert bestehende Tabellen (siehe [Datenbank](#datenbank)).

### Voraussetzungen in der Site-Konfiguration

Drei Punkte, ohne die die Plugins nicht (oder leer) rendern:

1. **Statisches Template der Extension einbinden**
   (`EXT:jw_feuser_manager/Configuration/Typoscript/`). Ohne das fehlt
   `tt_content.list.20.jwfeusermanager_*` und TYPO3 meldet
   *„No Content Object definition found at TypoScript object path …"*.

2. **Statisches Template von EXT:form einbinden**
   (`EXT:form/Configuration/TypoScript/`). Das Bearbeiten-Plugin baut sein Formular über
   das Form-Framework; ohne dessen TypoScript scheitert es mit
   *„The Prototype 'standard' …"* (`PrototypeNotFoundException`).

3. **Storage-PID setzen** — die Extension bringt keine Vorgabe mit:

   ```typoscript
   plugin.tx_jwfeusermanager.persistence.storagePid = <UID des Ordners mit den fe_users>
   ```

   Fehlt sie, sucht Extbase auf der Seite des Plugins und die Liste bleibt **leer, ohne
   Fehlermeldung**.

## Plugins

Die Extension stellt zwei Plugins bereit:

| Plugin | Controller | Actions | Zweck |
|---|---|---|---|
| **Liste von Benutzern** (`ListOfUsers`) | `ShowFeUserController` | `list`, `detail` | Mitgliederliste mit Gruppenfilter, Detailansicht, Exporte |
| **Benutzer bearbeiten** (`EditUser`) | `EditFeUserController` | `edit` | Anlegen und Bearbeiten von Benutzern über ein konfigurierbares Formular |

**Plugin-Namespace für Route-Enhancer und TypoScript: `tx_jwfeusermanager`**
(ohne Unterstrich zwischen „fe" und „user"). Der Namespace stammt noch von
`jw_frontendusermanager` und wurde bei der Umbenennung bewusst beibehalten, damit
bestehende URLs und Konfigurationen gültig bleiben.

Plugin-Signaturen: `jwfeusermanager_listofusers`, `jwfeusermanager_edituser`.

## Konfiguration

### TypoScript

Statisches Template einbinden. Voreinstellungen in `Configuration/Typoscript/setup.typoscript`:

```typoscript
plugin.tx_jwfeusermanager {
    view {
        templateRootPaths.1 = {$plugin.tx_jwfeusermanager.view.templateRootPath}
        partialRootPaths.1  = {$plugin.tx_jwfeusermanager.view.partialRootPath}
        layoutRootPaths.1   = {$plugin.tx_jwfeusermanager.view.layoutRootPath}
    }
    settings {
        orderBy = lastName,firstName
        fields  = last_name,first_name,email
    }
}
```

Für abweichende Templates die Konstanten `plugin.tx_jwfeusermanager.view.*RootPath` setzen.

### FlexForm — Plugin „Liste von Benutzern"

| Feld | Zweck |
|---|---|
| `groups`, `groupFiltering`, `groupFilteringSelect`, `groupConjunction` | Welche Benutzergruppen werden angezeigt, ob und wie gefiltert wird |
| `fields` | Angezeigte Felder (kommasepariert) |
| `editPage` | Seite mit dem Bearbeiten-Plugin |
| `useGroupTitle`, `useGroupLogo` | Gruppenüberschrift bzw. -logo in der Liste |
| `pdfDownload`, `pdfFields`, `pdfTitle`, `pdfLogo`, `pdfOrientation`, `pdfFontSize` | PDF-Export |
| `csvDownload`, `csvFields` | CSV-Export |
| `downloadFilename` | Dateiname der Exporte |

### FlexForm — Plugin „Benutzer bearbeiten"

| Feld | Zweck |
|---|---|
| `mode` | `0` = eigenen Benutzer bearbeiten, `1` = neuen Benutzer anlegen, `2` = Benutzer aus URL-Parameter `user` bearbeiten |
| `fields` | Angezeigte Felder |
| `setLastupdated` | Zeitstempel beim Speichern aktualisieren |
| `clearCachePages` | Seiten, deren Cache nach dem Speichern geleert wird |

> Ein früheres Feld „Dieses Element (ignorieren)" (`settings.uid`) ist entfallen. Es diente
> nur dazu, dem Code die UID des Inhaltselements durchzureichen — die ist über das
> Content-Object ohnehin verfügbar. Bei bestehenden Inhaltselementen bleibt der Wert
> ungenutzt im FlexForm-XML stehen; ein Migrationsschritt ist nicht nötig.

### Formularfelder (Plugin „Benutzer bearbeiten")

Welche Felder das Bearbeiten-Formular enthält, wird **nicht** im FlexForm gepflegt, sondern
über Datensätze vom Typ *Editor-Feld* (Tabelle `tx_jwfeusermanager_editorfield`) auf
derselben Seite. Jeder Datensatz beschreibt ein Formularfeld. Unterstützte Typen:

`TYPE_DB_FIELD`, `TYPE_DB_FIELD_READONLY`, `TYPE_PASSWORD`, `TYPE_IMAGE`,
`TYPE_ADDITIONAL_RICHTEXT`, `TYPE_ADDITIONAL_ENTRIES`, `TYPE_SEPARATOR`,
`TYPE_DELETE_USER`, `TYPE_USERGROUPS`, `TYPE_EMAIL`

Für Datenbankfelder stehen die Modi Text, Mehrzeilig, E-Mail, Boolean, Datum, Zeit,
Datum+Zeit, Mehrfachauswahl und Optionen zur Verfügung. Mehrfachauswahl und Optionen
werden als Bitfeld gespeichert.

## Datenbank

Die Extension **erweitert bestehende Tabellen**. Bei einem Rückbau gehen diese Daten verloren.

- **`fe_users`** — 23 zusätzliche Spalten: `mobilephone`, `phone_business`, `date_of_birth`,
  `tx_jwfeusermanager_newsletter_subscribed`, `tx_jwfeusermanager_lastupdated`, sowie je
  fünf `tx_jwfeusermanager_additional_text_1..5`, `_additional_boolean_1..5` und
  `_additional_bitfield_1..5`
- **`fe_groups`** — zusätzliche Spalte `image`
- **`tx_jwfeusermanager_editorfield`** — neue Tabelle für die Formularfeld-Definitionen

Die `_additional_*`-Felder sind der generische Mechanismus für projektspezifische
Mitgliederfelder, ohne die Extension anzupassen. Ihre Beschriftung wird über die
Editor-Feld-Datensätze gepflegt.

## ViewHelper

Namespace `JwTue\FeUserManager\ViewHelpers\` — Verzeichnis **`ViewHelpers`** (Plural).

| ViewHelper | Zweck |
|---|---|
| `Form\DateTimePickerViewHelper` | Datumsfeld im Bearbeitungsformular |
| `Format\PhoneViewHelper` | Telefonnummer formatieren |
| `Link\PhoneViewHelper` | Telefonnummer als `tel:`-Link |
| `Uri\PhoneViewHelper` | Telefonnummer als `tel:`-URI |

`Format\PhoneViewHelper::formatPhoneNumber()` ist statisch und wird auch aus den Controllern
für die Exporte verwendet.

## Exporte

| Format | Umsetzung |
|---|---|
| **PDF** | TCPDF (`tecnickcom/tcpdf`), Spalten aus `settings.pdfFields` |
| **CSV** | direkte Ausgabe, UTF-8, Spalten aus `settings.csvFields`; Bitfeld-Werte werden aufgelöst |
| **vCard** | mitgelieferte Bibliothek unter `Resources/Private/Library/vcard/`, Einzelperson aus der Detailansicht |

Gesteuert über den Parameter `download` (`pdf`, `csv`, `vcf`) an der `list`- bzw.
`detail`-Action.

## Entwicklung

### Statische Verifikation gegen TYPO3 v12

`Tests/verify-v12.php` prüft ohne laufenden Webserver, ob alle referenzierten Klassen und
die benutzten Methodensignaturen in einer echten TYPO3-v12-Installation existieren. Das
findet Portierungsfehler, die weder `php -l` noch eine IDE ohne `vendor/` erkennen.

```bash
TYPO3_ROOT=/pfad/zu/typo3-installation php Tests/verify-v12.php
```

Erwartet wird eine Composer-basierte TYPO3-12.4-Installation mit vollständigem `vendor/`.
Exit-Code 0 = keine Fehler, 1 = Fehler gefunden, 2 = `TYPO3_ROOT` fehlt oder ist ungültig.

Geprüft werden: Existenz aller referenzierten TYPO3-Klassen, Abwesenheit der in v12
entfernten APIs, die Signaturen, auf die sich der Code stützt, Ladbarkeit und Vererbung der
eigenen Klassen, Auflösbarkeit der Konstruktor-Abhängigkeiten sowie ein Scan aller
vollqualifizierten Klassenreferenzen im gesamten `Classes/`-Baum.

> **Der Test ersetzt kein Ausprobieren in einer laufenden Installation.** Er prüft, dass
> Klassen und Signaturen *existieren* — nicht Konfiguration, DI-Registrierung oder
> Laufzeitverhalten. Beim v12-Port meldete er „keine Fehler", während die Extension noch
> an einem Dutzend Stellen brach (fehlendes `Services.yaml`, falsche Plugin-Signatur,
> überschriebenes EXTBASEPLUGIN, fehlende Model-Eigenschaften). Nimm ein grünes Ergebnis
> also als Vorprüfung, nicht als Freigabe.

### Manueller Testpfad

Beim Ändern der Extension mindestens durchspielen:

1. Mitgliederliste rendert mit Daten
2. CSV-, PDF- und vCard-Export
3. Bearbeiten-Formular rendert alle konfigurierten Editor-Feldtypen
4. Benutzer anlegen — Passwort landet gehasht in der DB
5. Zusatzfeld (`tx_jwfeusermanager_additional_*`) wird gespeichert
6. `tx_jwfeusermanager_lastupdated` wird gesetzt, wenn `settings.setLastupdated` aktiv ist
7. Dublettenprüfung blockiert einen bereits vergebenen Benutzernamen — **auch wenn der
   bestehende Benutzer deaktiviert ist**
8. Bildupload: Datei landet im konfigurierten Ordner, `sys_file_reference` wird angelegt
9. Benutzer löschen: Datensatz auf `deleted=1`, Referenz und Datei entfernt, Weiterleitung
   auf die konfigurierte Seite

### Zusammenspiel mit `causal/image_autoresize`

Ist die Extension installiert, verkleinert sie hochgeladene Bilder **automatisch über
FAL-Events** — die Extension muss dafür nichts tun. Der frühere manuelle Aufruf von
`Slots\FileUpload` ist gegenstandslos: Diese Signal/Slot-API existiert seit v12 nicht mehr,
der Aufruf ist defensiv gekapselt und wird übersprungen.

Beim Testen zu beachten: Die Standardkonfiguration von `image_autoresize` hat einen
**Schwellwert von 400 KB** und Grenzen von 1920 × 1080. Kleinere Bilder bleiben unverändert
— das ist kein Fehler. Ein 4000 × 3000 großes Bild mit 12 MB wurde im Test korrekt auf
1440 × 1080 und 353 KB reduziert.

### Bekannte offene Punkte

- **`jeroendesloovere/vcard` ist als Abhängigkeit deklariert, wird aber nicht verwendet.**
  Der vCard-Export nutzt stattdessen die mitgelieferte Bibliothek unter
  `Resources/Private/Library/vcard/`. Entweder auf das Composer-Paket umstellen (dann kann
  die mitgelieferte Datei entfallen) oder die Abhängigkeit entfernen.
- **`EditorFieldRepository::findForElement()`** nutzt weiterhin `Query::statement()`. Das
  Model `EditorField` mappt weder `parent_ce` noch `sorting` als Eigenschaft, eine reguläre
  Extbase-Query kann darauf also weder filtern noch sortieren. Eine Umstellung setzt voraus,
  dass beide Felder zuvor im Model ergänzt werden.
- **Sechs eigene Formular-Elementtypen sind nirgends registriert.** Der Controller erzeugt
  `ImageCrop`, `LabeledStaticText`, `LabeledFluid`, `DateTimePicker`, `Fluid` und `Html`.
  EXT:form kennt keinen davon, und die Extension bringt **keine Form-YAML** mit. Weil im
  Standard-Prototyp `skipUnknownElements: true` gilt, wirft das **keinen Fehler** — die
  Elemente werden durch ein leeres Element ersetzt und rendern schlicht nichts.

  Praktisch heißt das: **kein Bild-Zuschnitt, kein Datumswähler, keine Trennlinien und
  kein Passwort-Generator-Knopf.** Der Rest des Formulars funktioniert.

  Die Registrierung muss also außerhalb der Extension liegen — vermutlich als
  `plugin.tx_form.settings.yamlConfigurations` im TypoScript der einbindenden
  Installation. In der v11-Fassung ist sie weder in der Extension noch in `fileadmin` zu
  finden; sie steht dort vermutlich in der Datenbank. **Vor einem Umzug muss diese
  Konfiguration gesichert werden**, sonst gehen die genannten Elemente verloren. Besser
  wäre, die YAML in die Extension zu holen.
- **Die Property-Namen der Zusatzfelder sind heikel.** `txJwfeusermanager*` (großes J) muss
  so bleiben: Der Setter-Aufruf im Speicher-Finisher, die Feldnamen-Vergleiche im
  `ShowFeUserController` und die `<f:case>`-Werte in `List.html` leiten sich alle über
  `underscoredToLowerCamelCase()` aus den Spaltennamen ab. Für die 15 durchnummerierten
  Felder reicht das nicht — deren Spalten stehen explizit in
  `Configuration/Extbase/Persistence/Classes.php`. Wer dort Felder ergänzt, muss den
  Eintrag mitpflegen, sonst werden sie stillschweigend nicht gespeichert.
- **Bildupload** ruft optional `Causal\ImageAutoresize\Slots\FileUpload` auf. Diese
  Signal/Slot-API existiert in aktuellen Versionen von `causal/image_autoresize` nicht mehr;
  der Aufruf ist defensiv gekapselt und wird dann übersprungen. Wenn die automatische
  Verkleinerung beim Upload gebraucht wird, muss auf die aktuelle API portiert werden.
- Es gibt **keine `ext_emconf.php`** — die Extension ist reine Composer-Extension und trägt
  keine Versionsnummer. Versioniert wird über Git.

## Historie

Die Extension entstand als Portierung von `jw_frontendusermanager` (TYPO3 v11) auf TYPO3 v12.
Beim Abschluss der Portierung wurden unter anderem ersetzt: `Extbase\Object\ObjectManager`
(in v12 entfernt), `ActionController::getViewProperty()` (entfernt), die Basisklassen
`Extbase\Domain\Repository\FrontendUser[Group]Repository` (entfernt) sowie
`ActionController::$extensionName` und `$contentObj` (nicht mehr vorhanden).
