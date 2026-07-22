# jw_feuser_manager

Manage **frontend users from the frontend** in TYPO3: a member list with group
filter and detail view, self-service editing of one's own profile, an editor for
administrators, and exports as PDF, CSV and vCard.

Successor to `jw_frontendusermanager` (TYPO3 v8–v11), which itself replaced
`datamints_feuser`. If you are coming from one of those, see
[Migrating from the predecessor](#migrating-from-the-predecessor).

| | |
|---|---|
| Extension key | `jw_feuser_manager` |
| Composer package | `jwtue/jw_feuser_manager` |
| TYPO3 | **14.x** (this branch/line) · 12.4 + 13.4 → the 13.x line |
| PHP | ≥ 8.2 |
| License | MIT |

> **Two release lines** (see [Versions & releases](#versions--releases)): the
> **14.x** line (branch `main`) supports TYPO3 v14; the **13.x** line (branch
> `13.x`) supports v12.4 + v13.4. Composer/TER pick the matching one automatically
> from your TYPO3 version. For **TYPO3 v11 and older**, use the predecessor
> `jw_frontendusermanager`.

## What you get

- **Member list** — lists frontend users of one or more groups, with optional
  group filter, group headings and group logos.
- **Detail view** — a single member's data on its own page.
- **Edit form** — users edit their own profile, or an administrator edits any
  user, or new users are created. Which fields the form contains is fully
  configurable per page.
- **Password change with confirmation of the current password** — see
  [The password field](#the-password-field).
- **Exports** — the member list as PDF or CSV, a single member as a vCard
  contact card.
- **Extra member fields** — up to 15 project-specific fields (text, yes/no,
  multiple-choice) without touching the extension's code.

## Installation

### With Composer (recommended)

```bash
composer require jwtue/jw_feuser_manager
```

Then, in the TYPO3 backend, open **Admin Tools → Maintenance → Analyze
Database Structure** and apply the suggested changes. The extension adds columns
to `fe_users` and `fe_groups` and creates one table (see [Database](#database)).

### From the TYPO3 Extension Repository (TER)

Install **JW Frontend User Manager** (`jw_feuser_manager`) via **Admin Tools →
Extensions**, then run the database analysis as above.

## Setting it up

The extension ships no ready-made pages. A working setup takes six steps.

### 1. Create a storage folder for the users

Create a **folder** (a page of type *Folder*) that will hold your `fe_users`
and `fe_groups` records, and note its page ID. All members live here.

### 2. Add the site set and point it at the storage folder

The extension ships a **site set** `jwtue/jw-feuser-manager` that carries all its
TypoScript (plugin configuration, the custom form elements, the plugin content
rendering) and depends on the Form Framework. Add it to your site configuration
(`config/sites/<identifier>/config.yaml`):

```yaml
dependencies:
  - typo3/fluid-styled-content
  - jwtue/jw-feuser-manager
```

Then **set the storage folder** from step 1 (page TSConfig or a small
`setup.typoscript` of your own site set):

```typoscript
plugin.tx_jwfeusermanager.persistence.storagePid = <UID of your storage folder>
```

If the set is missing, the plugins report *“Content Element … has no rendering
definition”*; if the storage PID is missing, the list stays **empty with no
error message** — the most common setup mistake.

### 3. Create the pages

Create the pages you need, typically:

- a **member list** page (e.g. `/members`),
- an **edit** page (e.g. `/edit-profile`),
- optionally a **detail** page — the detail view is shown by the *List of users*
  plugin itself, so a separate page is usually not required.

### 4. Add the plugins

The extension provides two plugins. Add them as content elements
(**Insert Plugin**):

| Plugin | Put it on | Purpose |
|---|---|---|
| **List of users** | the member list page | member list, detail view, exports |
| **Edit user** | the edit page | create / edit users via a configurable form |

### 5. Configure the “List of users” plugin

In the plugin's **Plugin** tab:

| Setting | What it does |
|---|---|
| Groups | Which user group(s) are listed |
| Group filtering / filter selection / conjunction | Whether visitors can filter by group, and how multiple groups are combined |
| Fields | Which columns are shown, comma-separated (e.g. `last_name,first_name,email`) |
| Edit page | The page that holds the *Edit user* plugin (used for the “edit” links) |
| Group title / group logo | Show a heading and logo per group |
| PDF download + PDF fields, title, logo, orientation, font size | Enable and shape the PDF export |
| CSV download + CSV fields | Enable the CSV export |
| Download file name | Base file name for the exports |

### 6. Configure the “Edit user” plugin and its form fields

In the plugin's **Plugin** tab, the key setting is the **mode**:

| Mode | Meaning |
|---|---|
| `0` | Edit the **currently logged-in** user (self-service) |
| `1` | **Create a new** user |
| `2` | Edit the user given in the URL parameter `user` (e.g. an admin editing from the list) |

Which fields the form contains is **not** set here — it is defined by
**“editor field” records** placed on the edit page. Create one record per form
field (record type *Editor field*, table `tx_jwfeusermanager_editorfield`). Each
record has a label, a type and — for database fields — the target `fe_users`
column and an input mode:

| Field type | Use for |
|---|---|
| Database field | A normal `fe_users` column (name, e-mail, phone, …) |
| Password | A password field — see below |
| Image | Profile photo with cropping |
| User groups | Let the user pick group membership |
| E-mail (notification) | Send an e-mail on save (e.g. a welcome mail) |
| Separator / static text / free Fluid | Layout and instructions inside the form |
| Delete user | A “delete my account” action |

Database fields support the input modes *text, multiline, e-mail, yes/no, date,
time, date + time, multiple choice* and *single choice*. Multiple/single choice
are stored as a bitfield.

#### The password field

When you add a **Password** editor field, the form renders:

- **Creating a new user** (mode `1`): *new password* + *repeat password*.
- **Editing an existing user** (mode `0` or `2`): *current password* + *new
  password* + *repeat password*.

For an existing user, the password is only changed when the **current password
is entered correctly**. A wrong or empty current password blocks the change and
shows an error — while leaving all three password fields empty simply keeps the
existing password and saves the rest of the form as usual.

## Clean URLs (routing)

Out of the box the extension's own links work — the detail, edit and download
links it renders carry the required `cHash`, so nothing else is needed for a
functioning site. They just look like `…/members?user=5&cHash=…`.

For **readable URLs** such as `/members/member/5`, add a route enhancer to your
site configuration (`config/sites/<identifier>/config.yaml`). The detail view,
the edit form and the vCard link all use the `user` parameter, so a single
enhancer covers them:

```yaml
routeEnhancers:
  FeUserDetail:
    type: Simple
    routePath: '/member/{user}'
    requirements:
      user: '[0-9]+'
```

With this in place the extension automatically generates
`/members/member/5` and `/edit-profile/member/5`. The enhancer is not restricted
to a page, so it applies wherever the `user` parameter appears. To scope it to
specific pages, add `limitToPages: [<uid>, …]`.

If you want the export links as file-like URLs too (e.g. `…/members.csv`), add a
`PageType` enhancer for the `download` parameter — this is optional; the default
query-string links work without it.

> **Manually typed short URLs 404.** A hand-written `…/members?user=5` without a
> `cHash` is rejected by TYPO3 — that is expected. Use the links the extension
> generates, or a route enhancer as above.

## Exports

| Format | Contents |
|---|---|
| **PDF** | the member list, columns from *PDF fields* |
| **CSV** | the member list, UTF-8, columns from *CSV fields* |
| **vCard** | a single member as a contact card, from the detail view |

PDF and CSV are offered on the list once enabled in the plugin; the vCard link
appears per member. Technically the exports are triggered by a `download`
parameter (`pdf`, `csv`, `vcf`) that the rendered links already carry.

## Migrating from the predecessor

If this installation previously ran `jw_frontendusermanager`, run the **upgrade
wizards** after installing and updating the database (**Admin Tools → Upgrade →
Upgrade Wizard**, or `vendor/bin/typo3 upgrade:run`):

- **Import legacy data** — copies your member data and editor-field definitions
  from the old `tx_jwfrontendusermanager_*` columns/table into the current
  `tx_jwfeusermanager_*` structure, and rewrites the editor fields' target
  columns to the new names. Without this, the extra member fields would display
  but silently fail to save. The wizard is non-destructive (old columns are
  kept) and can be run repeatedly.
- **Migrate list_type plugins to CType** — TYPO3 v14 replaced the `list_type`
  mechanism with dedicated CTypes. This wizard rewrites existing content elements
  (`CType=list`, `list_type=jwfeusermanager_*`, including the predecessor
  signatures) to `CType=jwfeusermanager_listofusers` / `_edituser`. It reads the
  `list_type` column directly — which is still physically present after a
  v13 → v14 upgrade, because TYPO3 never drops columns automatically (that is a
  separate, confirmed step in the database analyzer). Run the wizard **before**
  removing the now-unused `list_type` column. On a fresh v14 install the column
  does not exist and the wizard is a no-op.

Both wizards only act where legacy data is actually present, so they are safe to
run on a fresh install too.

### Upgrading TYPO3 v13 → v14

1. `composer require jwtue/jw_feuser_manager:^14` together with the TYPO3 v14
   upgrade (Composer resolves the 14.x line automatically).
2. Run the **database analyzer** (adds new fields; keeps `list_type`).
3. Run the **upgrade wizards** — in particular *Migrate list_type plugins to
   CType* above.
4. Add the site set to your site configuration (see
   [Setting it up, step 2](#2-add-the-site-set-and-point-it-at-the-storage-folder)).
5. Optionally remove the unused `list_type` column in the database analyzer.

## Versions & releases

The extension is maintained in **two parallel lines**, released in parallel to
GitHub, Packagist and the TER:

| Line | Branch | TYPO3 | Release tags |
|---|---|---|---|
| **14.x** | `main` | v14 | `v14.x.y` |
| **13.x** | `13.x` | v12.4 + v13.4 | `v13.x.y` |

`composer require jwtue/jw_feuser_manager` (or the TER) always installs the
version matching your TYPO3 — the 13.x line on v12/v13, the 14.x line on v14.
Bug fixes are made on the oldest affected line and forward-ported. Pushing a
version tag on either branch builds the archive and publishes that version (see
`.github/workflows/release.yml`); the `ext_emconf.php` version must match the tag.

## Extra member fields

Beyond the standard `fe_users` columns the extension provides 15 generic fields
for project-specific data — five each of **text**, **yes/no** and
**multiple-choice** (`tx_jwfeusermanager_additional_text_1..5`,
`_additional_boolean_1..5`, `_additional_bitfield_1..5`). Give them a meaningful
label via an editor field record and use them like any other field. This lets
you add member attributes (rank, driving licences, availability, …) without
modifying the extension.

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| List is **empty, no error** | `storagePid` not set, or points to the wrong folder (step 2) |
| *“No Content Object definition found …”* | static template of this extension not included (step 2.1) |
| *“The Prototype 'standard' …”* | static template of **EXT:form** not included (step 2.2) |
| Image preview / cropping / date picker **missing** | static template of this extension not included — the custom form elements are registered there |
| Extra fields **display but don't save** (after a move from the predecessor) | run the *Import legacy data* upgrade wizard (see [Migrating](#migrating-from-the-predecessor)) |
| `…?user=5` gives a **404** | hand-written URL without `cHash` — use the generated links or a route enhancer |

## Database

The extension **extends existing tables** — this data is lost if the extension
is removed.

- **`fe_users`** — additional columns: `mobilephone`, `phone_business`,
  `date_of_birth`, a newsletter flag, a “last updated” timestamp, plus the 15
  `tx_jwfeusermanager_additional_*` fields.
- **`fe_groups`** — additional column `image` (group logo).
- **`tx_jwfeusermanager_editorfield`** — new table holding the form-field
  definitions.

## Development

Internals, the porting history, the static verification harness and the
repository conventions are documented in **[AGENTS.md](AGENTS.md)**. In short:

- Templates and the programmatically built form live under
  `Resources/Private/`; the custom form elements are registered in
  `Configuration/Yaml/FormSetup.yaml`.
- `Tests/verify-v12.php` statically checks class and signature existence against
  a real TYPO3 installation — a preliminary check, not a substitute for testing
  in a running site.

When changing the extension, at least verify: the member list renders; CSV, PDF
and vCard export; the edit form renders every configured field type; creating a
user hashes the password; the current-password check blocks a password change;
the duplicate-username check blocks a taken name (even a disabled one); image
upload and user deletion work.

## Notes on AI assistance

The predecessor `jw_frontendusermanager` was written by hand. The port to TYPO3
v12, the v13 compatibility work, the current-password confirmation, the upgrade
wizards, the TYPO3 v14 port (ViewFactory, CTypes, site set, PSR-7 request
attributes, list_type → CType migration) and this documentation were carried out
with [Claude Code](https://www.claude.com/product/claude-code) and verified
against running TYPO3 12.4, 13.4 and 14.x installations before release. See
[AGENTS.md](AGENTS.md) for how the repository is set up for that work.
