# AGENTS.md — working on jw_feuser_manager

Guidance for AI assistants and contributors working in this repository.

## What this extension is

A TYPO3 extension for managing frontend users **from the frontend**: a member list, a detail
view, self-service editing, and export as PDF, CSV and vCard. It is the successor to
`jw_frontendusermanager` (TYPO3 v8–v11).

**Two release lines:** this file is on branch `13.x`, which targets **TYPO3 v12.4 + v13.4**.
TYPO3 **v14** is on branch `main` (its own AGENTS.md describes the v14 specifics: CTypes instead
of list_type, ViewFactory instead of StandaloneView, PSR-7 request attributes instead of
`$GLOBALS['TSFE']`, a site set). Bug fixes are made here and forward-ported to `main`; releases
go out in parallel as `v13.x.y` (here) and `v14.x.y` (`main`).

Two plugins, registered in `ext_localconf.php` via `configurePlugin('JwFeUserManager', …)`:

| Plugin | Controller | Actions |
|---|---|---|
| `ListOfUsers` | `Classes/Controller/ShowFeUserController.php` | `list`, `detail` |
| `EditUser` | `Classes/Controller/EditFeUserController.php` | `edit` |

The **plugin namespace is `tx_jwfeusermanager`** (the extension key without underscores). Route
enhancers and TypoScript use it. Do not change it — it is also the database prefix.

## Naming and the database prefix

Repo, composer package, extension key and namespace are all `jw_feuser_manager` /
`JwTue\FeUserManager`. The DB prefix `tx_jwfeusermanager` is the key without underscores — this is
the TYPO3 convention, not an inconsistency, even though it reads oddly next to `FeUserManager`.

The predecessor used the prefix `jwfrontendusermanager`. Two upgrade wizards in `Classes/Updates/`
migrate an existing installation (plugin signatures and member data). They are registered via the
`#[UpgradeWizard('…')]` attribute plus autoconfiguration in `Configuration/Services.yaml` — **not**
via `SC_OPTIONS`, which would instantiate them without dependency injection and fail on the
`ConnectionPool` constructor argument.

## Things that will bite you

- **Property naming `txJwfeusermanager*` (capital J).** Extbase derives the column name from the
  property via `underscoredToLowerCamelCase()`. The same casing is compared against field names in
  `ShowFeUserController` and in `Resources/Private/Templates/ShowFeUser/List.html`. Keep it
  consistent or those comparisons silently stop matching.
- **The 15 numbered additional fields** (`…_additional_text_1` … `_bitfield_5`) do not round-trip
  through the automatic name derivation (`…Text1` → `…_text1`, not `…_text_1`). Their columns are
  mapped explicitly in `Configuration/Extbase/Persistence/Classes.php`. Add fields there too, or
  they are not persisted.
- **`Domain/Model/AbstractFrontendUser`** re-implements the base properties (username, firstName,
  …) that `TYPO3\CMS\Extbase\Domain\Model\FrontendUser` used to provide — that class is removed in
  v12. `FrontendUser` extends it and adds the extension-specific fields.
- **`FrontendUserRepository::findForUsername()`** must see disabled/expired users
  (`setIgnoreEnableFields(true)`, `setRespectStoragePage(false)`); it backs the duplicate check when
  creating users. Do not "simplify" that away.
- **The edit form uses EXT:form.** Its static TypoScript must be included, and a `storagePid` must
  be set, or the plugins render empty/broken. See the README's requirements.

## Testing

`Tests/verify-v12.php` is a reflection check: point it at a real, Composer-based TYPO3 12.4
installation (with a full `vendor/`) via the `TYPO3_ROOT` environment variable, and it verifies
that all referenced classes and the method signatures the code relies on actually exist in v12.
It finds porting errors that neither `php -l` nor an IDE without `vendor/` catches.

```bash
TYPO3_ROOT=/path/to/typo3-install php Tests/verify-v12.php
```

It does **not** replace running the extension. Before release, verify in a real installation:
member list with data, the edit form rendering all configured editor-field types, creating a user
(password ends up hashed), a numbered additional field being saved, the `lastupdated` timestamp,
and the duplicate check blocking an already-taken username **even when the existing user is
disabled**.

## Repository conventions

- **Language: English** for code comments, README and this file. Frontend-facing UI strings
  (member-visible labels in the controllers, Fluid templates and form setup) stay German — this
  extension is used on German-language sites.
- **No AI attribution in commit messages.** Do not add `Co-Authored-By: Claude …` trailers. AI
  assistance is disclosed transparently in prose (README), not in commit metadata.
