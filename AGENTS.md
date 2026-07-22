# AGENTS.md — working on jw_feuser_manager

Guidance for AI assistants and contributors working in this repository.

## What this extension is

A TYPO3 extension for managing frontend users **from the frontend**: a member list, a detail
view, self-service editing, and export as PDF, CSV and vCard. It is the successor to
`jw_frontendusermanager` (TYPO3 v8–v11).

**Two release lines** (see [Branches & releases](#branches--releases)): branch `main` targets
**TYPO3 v14**, branch `13.x` targets **v12.4 + v13.4**. This file lives on `main` and describes
the v14 line; where the two diverge it is called out.

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
- **The edit form uses EXT:form.** The Form Framework TypoScript must be present (the site set
  `jwtue/jw-feuser-manager` depends on `typo3/form`), and a `storagePid` must be set, or the
  plugins render empty/broken. See the README's requirements.

## v14 specifics (branch `main`)

- **Plugins are dedicated CTypes**, not `list_type`. `Configuration/TCA/Overrides/tt_content.php`
  registers them via `ExtensionUtility::registerPlugin()` with the icon + FlexForm arguments; the
  CType names equal the former `list_type` values. Existing content is migrated by the
  `jwFeUserManager_listTypeToCType` wizard, which reads the still-present `list_type` column
  directly (guarded by a column-existence check).
- **No StandaloneView.** v14 removed it. Both controllers use the view that the ActionController
  builds via the `ViewFactory` (`$this->view`, with the extension's root paths and
  controller/action already set); `initializeView()` only assigns extras. The edit form is
  rendered through the EXT:form `FormRuntime`, not through the Fluid view.
- **No `$GLOBALS['TSFE']`.** Use the PSR-7 request attributes: `frontend.user` (fe user),
  `frontend.page.information->getId()` (page uid, via `currentPageUid()`), `currentContentObject`.
- **Site set** `Configuration/Sets/JwFeUserManager/` carries the plugin TypoScript, the form
  element YAML, the CSS and the CType content rendering. There is no static `Configuration/TypoScript/`
  or `sys_template` registration on this line.
- **ViewHelpers** use instance `render(): string` (v14 removed `renderStatic()` + the
  `CompileWith…` trait, `registerTagAttribute()` and `registerUniversalTagAttributes()`).

## Branches & releases

Two parallel lines, released in parallel to GitHub, Packagist and the TER:

| Line | Branch | TYPO3 | Tags |
|---|---|---|---|
| 14.x | `main` | v14 | `v14.x.y` |
| 13.x | `13.x` | v12.4 + v13.4 | `v13.x.y` |

Bug fixes are made on the oldest affected line and forward-ported (the business logic — password
feature, exports, migrations — is identical; only the infrastructure differs). Pushing a `v*` tag
on either branch triggers `.github/workflows/release.yml`, which builds the archive and publishes
that version; **`ext_emconf.php` version must equal the tag** (the workflow enforces this).

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
