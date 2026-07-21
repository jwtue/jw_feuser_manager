# jw_feuser_manager

TYPO3 extension for managing **frontend users in the frontend**: member list,
detail view, self-service editing of one's own data, plus export as PDF, CSV and vCard.

Successor to the `jw_frontendusermanager` extension (TYPO3 v8–v11), which itself
replaced `datamints_feuser`.

| | |
|---|---|
| Extension key | `jw_feuser_manager` |
| Composer package | `jwtue/jw_feuser_manager` |
| PHP namespace | `JwTue\FeUserManager\` |
| TYPO3 | 12.4 — **not** compatible with v11, see below |
| PHP | ≥ 8.1 |
| License | MIT |

> **This edition runs exclusively under TYPO3 v12.** It is the port of
> `jw_frontendusermanager`, which remains the v11 edition. For a v11 installation,
> use the predecessor extension, not this one.
>
> In several places the code uses APIs that do not exist in v11 — in particular the
> PSR-7-based Extbase request (`$this->request->getQueryParams()`,
> `->getAttribute('currentContentObject')`) and `getRenderingContext()->setControllerName()`
> on the StandaloneView. The v11 edition used `GeneralUtility::_GET()`,
> `ConfigurationManager::getContentObject()` and `Extbase\Mvc\View\ViewInterface` for this — the
> latter no longer exists in v12. An extension serving both would only be possible with
> version switches; this was deliberately not done.

## Installation

```bash
composer require jwtue/jw_feuser_manager:dev-main
```

The package is not on Packagist. The consuming TYPO3 installation therefore needs
a VCS entry in its `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/jwtue/jw_feuser_manager.git" }
]
```

After installation, run the database comparison in the Install Tool — the extension
extends existing tables (see [Database](#database)).

### Prerequisites in the site configuration

Three points, without which the plugins will not render (or render empty):

1. **Include the extension's static template**
   (`EXT:jw_feuser_manager/Configuration/TypoScript/`). Without it,
   `tt_content.list.20.jwfeusermanager_*` is missing and TYPO3 reports
   *"No Content Object definition found at TypoScript object path …"*.

2. **Include the static template of EXT:form**
   (`EXT:form/Configuration/TypoScript/`). The edit plugin builds its form via
   the Form Framework; without its TypoScript it fails with
   *"The Prototype 'standard' …"* (`PrototypeNotFoundException`).

3. **Set the storage PID** — the extension ships no default:

   ```typoscript
   plugin.tx_jwfeusermanager.persistence.storagePid = <UID of the folder holding the fe_users>
   ```

   If it is missing, Extbase searches on the plugin's page and the list stays **empty, with no
   error message**.

## Plugins

The extension provides two plugins:

| Plugin | Controller | Actions | Purpose |
|---|---|---|---|
| **List of users** (`ListOfUsers`) | `ShowFeUserController` | `list`, `detail` | Member list with group filter, detail view, exports |
| **Edit user** (`EditUser`) | `EditFeUserController` | `edit` | Creating and editing users via a configurable form |

**Plugin namespace for route enhancers and TypoScript: `tx_jwfeusermanager`**
(no underscore between "fe" and "user"). The namespace still originates from
`jw_frontendusermanager` and was deliberately kept during the rename so that
existing URLs and configurations remain valid.

Plugin signatures: `jwfeusermanager_listofusers`, `jwfeusermanager_edituser`.

## Configuration

### TypoScript

Include the static template. Defaults in `Configuration/TypoScript/setup.typoscript`:

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

For different templates, set the constants `plugin.tx_jwfeusermanager.view.*RootPath`.

### FlexForm — "List of users" plugin

| Field | Purpose |
|---|---|
| `groups`, `groupFiltering`, `groupFilteringSelect`, `groupConjunction` | Which user groups are shown, whether and how filtering is applied |
| `fields` | Displayed fields (comma-separated) |
| `editPage` | Page containing the edit plugin |
| `useGroupTitle`, `useGroupLogo` | Group heading and group logo in the list |
| `pdfDownload`, `pdfFields`, `pdfTitle`, `pdfLogo`, `pdfOrientation`, `pdfFontSize` | PDF export |
| `csvDownload`, `csvFields` | CSV export |
| `downloadFilename` | File name of the exports |

### FlexForm — "Edit user" plugin

| Field | Purpose |
|---|---|
| `mode` | `0` = edit own user, `1` = create new user, `2` = edit user from URL parameter `user` |
| `fields` | Displayed fields |
| `setLastupdated` | Update timestamp on save |
| `clearCachePages` | Pages whose cache is cleared after saving |

> A former field "This element (ignore)" (`settings.uid`) has been removed. It only served
> to pass the UID of the content element through to the code — which is available via the
> content object anyway. For existing content elements the value remains in the FlexForm XML
> unused; no migration step is required.

### Custom form element types

The edit plugin builds its form programmatically and uses six
element types that EXT:form does not ship:

| Type | Purpose |
|---|---|
| `ImageCrop` | Image preview with cropping (cropper.js) |
| `DateTimePicker` | Date field |
| `LabeledStaticText` | static text with label |
| `LabeledFluid` / `Fluid` | free Fluid with or without label |
| `Html` | raw HTML block (separators, password generator) |

They are registered in `Configuration/Yaml/FormSetup.yaml`; the Fluid partials live
under `Resources/Private/Form-Frontend/Partials/`. The YAML is included via
`plugin.tx_form.settings.yamlConfigurations` in the extension's static TypoScript.

> **Why this matters:** In the standard prototype of EXT:form,
> `skipUnknownElements: true` applies. If the registration is missing, TYPO3 throws **no error** —
> the elements are replaced by an empty element and simply render nothing. This is exactly
> how the image preview, cropping and date picker disappeared after the v12 port, without
> anything being logged anywhere. Whoever forgets to include the static TypoScript
> gets the same picture.

### Form fields ("Edit user" plugin)

Which fields the edit form contains is **not** maintained in the FlexForm, but
via records of type *editor field* (table `tx_jwfeusermanager_editorfield`) on
the same page. Each record describes one form field. Supported types:

`TYPE_DB_FIELD`, `TYPE_DB_FIELD_READONLY`, `TYPE_PASSWORD`, `TYPE_IMAGE`,
`TYPE_ADDITIONAL_RICHTEXT`, `TYPE_ADDITIONAL_ENTRIES`, `TYPE_SEPARATOR`,
`TYPE_DELETE_USER`, `TYPE_USERGROUPS`, `TYPE_EMAIL`

For database fields, the modes Text, Multiline, Email, Boolean, Date, Time,
Date+Time, Multiple selection and Options are available. Multiple selection and options
are stored as a bitfield.

## Database

The extension **extends existing tables**. On removal, this data is lost.

- **`fe_users`** — 23 additional columns: `mobilephone`, `phone_business`, `date_of_birth`,
  `tx_jwfeusermanager_newsletter_subscribed`, `tx_jwfeusermanager_lastupdated`, plus five each of
  `tx_jwfeusermanager_additional_text_1..5`, `_additional_boolean_1..5` and
  `_additional_bitfield_1..5`
- **`fe_groups`** — additional column `image`
- **`tx_jwfeusermanager_editorfield`** — new table for the form field definitions

The `_additional_*` fields are the generic mechanism for project-specific
member fields without modifying the extension. Their labels are maintained via the
editor field records.

## ViewHelpers

Namespace `JwTue\FeUserManager\ViewHelpers\` — directory **`ViewHelpers`** (plural).

| ViewHelper | Purpose |
|---|---|
| `Form\DateTimePickerViewHelper` | Date field in the edit form |
| `Format\PhoneViewHelper` | Format phone number |
| `Link\PhoneViewHelper` | Phone number as a `tel:` link |
| `Uri\PhoneViewHelper` | Phone number as a `tel:` URI |

`Format\PhoneViewHelper::formatPhoneNumber()` is static and is also used from the controllers
for the exports.

## Exports

| Format | Implementation |
|---|---|
| **PDF** | TCPDF (`tecnickcom/tcpdf`), columns from `settings.pdfFields` |
| **CSV** | direct output, UTF-8, columns from `settings.csvFields`; bitfield values are resolved |
| **vCard** | bundled library under `Resources/Private/Library/vcard/`, single person from the detail view |

Controlled via the parameter `download` (`pdf`, `csv`, `vcf`) on the `list` or
`detail` action.

## Development

### Static verification against TYPO3 v12

`Tests/verify-v12.php` checks, without a running web server, whether all referenced classes and
the used method signatures exist in a real TYPO3 v12 installation. This
catches porting errors that neither `php -l` nor an IDE without `vendor/` detects.

```bash
TYPO3_ROOT=/pfad/zu/typo3-installation php Tests/verify-v12.php
```

A Composer-based TYPO3 12.4 installation with a complete `vendor/` is expected.
Exit code 0 = no errors, 1 = errors found, 2 = `TYPO3_ROOT` missing or invalid.

Checked are: existence of all referenced TYPO3 classes, absence of the APIs removed in v12,
the signatures the code relies on, loadability and inheritance of the
own classes, resolvability of the constructor dependencies, plus a scan of all
fully qualified class references throughout the entire `Classes/` tree.

> **The test is no substitute for trying it out in a running installation.** It checks that
> classes and signatures *exist* — not configuration, DI registration or
> runtime behavior. During the v12 port it reported "no errors" while the extension still
> broke in a dozen places (missing `Services.yaml`, wrong plugin signature,
> overridden EXTBASEPLUGIN, missing model properties). So take a green result
> as a preliminary check, not as a release approval.

### Manual test path

When changing the extension, at least run through:

1. Member list renders with data
2. CSV, PDF and vCard export
3. Edit form renders all configured editor field types
4. Create user — password ends up hashed in the DB
5. Additional field (`tx_jwfeusermanager_additional_*`) is saved
6. `tx_jwfeusermanager_lastupdated` is set when `settings.setLastupdated` is active
7. Duplicate check blocks an already taken username — **even if the
   existing user is disabled**
8. Image upload: file ends up in the configured folder, `sys_file_reference` is created
9. Delete user: record set to `deleted=1`, reference and file removed, redirect
   to the configured page

### Interaction with `causal/image_autoresize`

If the extension is installed, it downsizes uploaded images **automatically via
FAL events** — the extension has to do nothing for this. The former manual call to
`Slots\FileUpload` is moot: this signal/slot API no longer exists since v12,
the call is defensively wrapped and skipped.

To keep in mind when testing: the default configuration of `image_autoresize` has a
**threshold of 400 KB** and limits of 1920 × 1080. Smaller images stay unchanged
— this is not a bug. A 4000 × 3000 image of 12 MB was correctly reduced in the test to
1440 × 1080 and 353 KB.

### Known open issues

- **`jeroendesloovere/vcard` is declared as a dependency but is not used.**
  The vCard export instead uses the bundled library under
  `Resources/Private/Library/vcard/`. Either switch to the Composer package (then
  the bundled file can be dropped) or remove the dependency.
- **`EditorFieldRepository::findForElement()`** still uses `Query::statement()`. The
  `EditorField` model maps neither `parent_ce` nor `sorting` as a property, so a regular
  Extbase query can neither filter nor sort on them. A switchover requires
  that both fields first be added to the model.
- **No HTML with curly braces in `content` properties.** The partials
  `Fluid` and `LabeledFluid` render their content via `v:render.inline`, that is **as
  Fluid source code**. Embedded JavaScript is thereby torn apart: `{` starts a Fluid
  expression. This is exactly what the password generator failed on previously. Whoever needs
  such building blocks creates a custom element type with a partial (model:
  `PasswordGenerator`) and moves the JavaScript out into a file.
- **The property names of the additional fields are delicate.** `txJwfeusermanager*` (capital J) must
  stay that way: the setter call in the save finisher, the field name comparisons in the
  `ShowFeUserController` and the `<f:case>` values in `List.html` are all derived via
  `underscoredToLowerCamelCase()` from the column names. For the 15 numbered
  fields this is not enough — their columns are listed explicitly in
  `Configuration/Extbase/Persistence/Classes.php`. Whoever adds fields there must maintain the
  entry too, otherwise they are silently not saved.
- **Image upload** optionally calls `Causal\ImageAutoresize\Slots\FileUpload`. This
  signal/slot API no longer exists in current versions of `causal/image_autoresize`;
  the call is defensively wrapped and is then skipped. If automatic
  downsizing on upload is needed, it must be ported to the current API.
- The extension ships an `ext_emconf.php` (version `12.0.0`, TYPO3 12.4, PHP 8.1) alongside
  `composer.json`, so it is described for both the classic Extension Manager and Composer.

## History

The extension originated as a port of `jw_frontendusermanager` (TYPO3 v11) to TYPO3 v12.
On completing the port, the following were replaced among others: `Extbase\Object\ObjectManager`
(removed in v12), `ActionController::getViewProperty()` (removed), the base classes
`Extbase\Domain\Repository\FrontendUser[Group]Repository` (removed) as well as
`ActionController::$extensionName` and `$contentObj` (no longer present).

## Development notes

The predecessor `jw_frontendusermanager` was written by hand. The TYPO3 v12 port and the
subsequent work — making the extension runnable again, the migration upgrade wizards, the
`ext_emconf.php`, and the English translation of comments and this README — were carried out
with [Claude Code](https://www.claude.com/product/claude-code). All changes were verified
against a running TYPO3 12.4 installation (rendering the member list, the edit form and the
exports) before release. See [AGENTS.md](AGENTS.md) for how the repository is set up for that
work.
