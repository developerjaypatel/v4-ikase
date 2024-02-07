# Overall things to improve in the project structure

## File structure

- [ ] check about merging upload folders in a single place:
  - [ ] pdfimage and pdf_image
  - [ ] scans
  - [ ] fromclient
  - [ ] uploads
  - [ ] export?

- [ ] move sessions out of the filesystem (/sessions, /sessions2)
  - [ ] memory, memcache?
  - [ ] remove session data from login URLs as well

- [ ] move all temp folders out of the repository and into the OS temp dir
- [ ] check uses of error_reporting and ideally log everything, routed to external logs in production (and to screen in dev)

## Ideally, before migrating to git

- [ ] check references to ikase.website and replace by a constant of ikase.website/ikase.org, depending on...? or maybe just use some $_SERVER value?
- [x] try to fix first and second commits, so there are no dangling files in the first one
- [ ] cleanup/optimize large files:
  - [x] `images/home` (optimized to 40% JPG)
  - [x] `iklock/images` (including the huge gifs)
  - [x] `remind/images`
  - [x] `remind/developer/images`
  - [ ] what's `api/bin`? that got ignored for now
- [x] ignore several files
  - [x] everything inside the upload folders
  - [x] session folders
  - [x] /third/*.(mdb|sql)
- [ ] bring back "upload" files that are actually used by the system (e.g. `uploads/envelope_info.docx` at `createLetterEnvelope()`)
- [x] remove the following files:
  - [x] .cprestoretmp*
  - [x] Microsoft/PowerShell/CommandAnalysis
- [ ] disable iKlock access in production
- [ ] move dependencies into Composer and NPM (or a specific folder if not available in dependency managers)
  - [ ] side-effect: all dependencies in a single folder
  - [ ] possibly repeated dependencies:
    - [ ] /sendit, /sms/sendit, /remind/sendit
    - [ ] /isotope-docs
    - [ ] phpseclib (`remind/api/Crypt`)
    - [ ] `Math_BigInteger`: pretty sure this is not needed anymore, besides being broken
    - [ ] `Net_SFTP`, `Net_SCP`, `Net_SSH1`, `Net_SSH2`: probably could be moved to Composer?
      - [ ] should probably be dropped if not in use, or migrated to another package, as they depend on deprecated/removed `mcrypt` through the `Crypt_AES` package
  - [ ] Known dependencies:
    - [x] Slim
    - [ ] PHPExcel-1.8
    - [ ] PHPMailer
    - [ ] cleditor
    - [ ] phpOCR
    - [x] phpdocx_pro - IMPORTANT, CONTAINS BINARY FILES
    - [ ] sendit
    - [ ] isotope
    - [ ] `cls_fileupload.php`
    - [ ] `remind/flot`
    - [ ] Plivo?
    - [ ] Authorize.NET API
    - [ ] libcharts - seems to have ancient PHP code, and some are even broken
    - [ ] jquery-dialogextend
    - [ ] editable_select
    - [ ] fullcalendar

## Migration to PHP 7

- [x] Try to run the project locally and adapt it where possible
  - [x] Write docs about running the test suite
- [x] Upgrade unsupported dependencies
  - [x] [Slim 2](http://www.slimframework.com/2012/09/13/version-2.html#how-to-upgrade-older-applications)
    - home-made additions to Slim 1.6.0 that will be lost for now (stored at `api/slim-changes.patch`):
      - something related to magic quotes on $_POST?
      - Request::delete()
      - treatment of host field with port
      - improvement of IP capture
      - use of hash_hmac() instead of MD5 for secure cookie IV
      - load of flash messages from session?
      - SessionCookie::loadSession() forces session to start, and to be destroyed on saveSession()
      - improvement on load of env SCRIPT_NAME and trim of $_SERVER values
      - actual use of log level in Log::log() and LogFileWriter::write()
  - [x] [Slim 3](http://www.slimframework.com/docs/v3/start/upgrade.html)
  - [x] Slim 4
    - [x] it seems some (all?) functions are missing the `($req, $res, $arg1, $arg2)` signature change (i.e. `getLetter()`)
  - [x] PhpDocxPro depends on two deprecated features. Check the package's To-Do list
- [ ] enable E_DEPRECATED error reporting
  - [x] API entrypoints - done through `/bootstrap.php`
  - [ ] other pieces of code - there's also an "Important task" below about merging all those `error_reporting()` definitions
  - [ ] implement something like Rollbar to track PHP error logging and squash down most common notices, warnings and whatnot - besides actually tracking deprecated notices
- [ ] to PHP 7.0
  - [x] `ereg_*`, `eregi_*`, `split` functions
  - [x] fix old-style constructors - some are left, but in PHPUnit tests of ancient dependencies (sendit)
  - [ ] replace mysql_*
    - ~~try to check about adding Eloquent instead~~ - no way, the codebase is way too fragmented for this to be feasible in this phase
    - [x] remove all reset_link() methods from iklock/classes, as they weren't in use and used the old mysql connection
    - [x] try to use a single database connection file for all entrypoints (or a configurable one through globals?)
    - [x] use `DB_HOST` in all possible cases (i.e. reduce the number of options in `db()`)
    - [ ] some files couldn't be migrated:
      - [ ] check files that require `eamsjetfiler/datacon.php`:
        - `api/carriers_update_data.php`
        - `iklock/manage/users/user_list_all.php`
        - a couple others
    - [ ] write tests for files changed related to `md_reminder` - that requires a dump from that database, which is not accessible on the dev server. Example files:
      - [ ] remind/import.php
      - [ ] remind/manage/customers/update.php
    - [x] write tests for the `DB` class?
    - [ ] RegExp replacements roadmap:
      - [ ] cleanup `cls_*` uses (depended on external connection passed into the object)
        - mysql functions seems to have been forgotten in unused methods, while actual used methods (less than three in any of the classes) were already migrated to PDO (old methods shouldn't even be working, as there's no place that feds in the `datalink` attribute):
          - cls_document_matrix
          - cls_events
          - cls_notes
          - cls_person
          - cls_department
          - cls_address
          - cls_comm
          - cls_user
          - cls_eventscalendar
          - not in use whatsoever:
            - cls_webcalendar
            - cls_displaycalendar
            - cls_popupcalendar
            - cls_calendar_dropdown
      - [x] `mysql_query() or die()` becomes `DB::runOrDie()`
      - [x] `for...$numbs` becomes `while ($row = $result->fetch())`
        - [x] basic run
        - [x] missed cases using `$x` instead of `$i` and other craziness like `reports/demographics_injury_sheet.php:219` - maybe check for `$numbs` use?
      - [x] `mysql_result()` becomes `$row[]`
        - [x] basic run
        - [x] there are missed cases (probably for using complex counter variables? - `reports/demographics_sheet_mobile.php`)
        - [x] there are cases where there's no `for`, and thus `$row` became undefined - tip: they're usually accompanied by a copy-pasta-hack `$int=0`, like at `remind/developer/manage/customers/editor.php`
          - [x] S&R for `\$int\s*=\s*0`
          - [x] inspect all replacements done with this idiom to make sure they all have a defined `$row
        - [x] check if there are uses of subsequent `fetchColumn()` (that actually won't work)
      - [x] `mysql_fetch_assoc(.*)` becomes `$$1->fetch()`
      - [x] `mysql_insert_id()` becomes `DB::lastInsertId()`
      - [x] check if `$numbs` is still used, so it needs to be upgraded too
      - [x] check for missed `for`'s
      - [x] any other `mysql_*` uses?
      - [x] use `PDO::FETCH_OBJECT` as the default instead, so it fits with the current PDO uses
        - [x] check for weird uses of `$row` with keys with spaces (e.g. `[remind/|iklock/]manage/cards/card_list.php`)
      - [x] make sure all places have access to the new classes
        - [x] can we use `auto_prepend_file`?
        - [x] remove old autoload requires
      - [x] write tests
      - [x] check if there's other `or die` uses left
      - [x] what other cases are there to replace? certainly many are not covered by that approach
      - [x] is it possible to replace common, raw PDO uses as well? e.g. `->execute()`, `->prepare()`, `->lastInsertId()`
      - [x] remove useless clearing calls (`closeCursor()`, `close()`, `$stmt = null`, `$db = null`, `mysql_close()`)
      - [x] make sure all `DB` calls are covered by a `bootstrap.php` require...
      - [x] ...and make sure they still point to the correct database connection
      - [x] make sure there's at least one test covering use of `fetchColumn()` (never used it before)
        - all real use cases are either too complex to cover now, or involve HTML files. However, that method is used in `DBTest::testRun()` in the same way it's used elsewhere, so it's safe to say it works as expected during the S&R.
    - [x] try to replace further the legacy code [[see caveat]](#updating-legacy-or-insecure-sql)
  - [x] replace mysqli_* with PDO? [[see caveat]](#mysqli-replacement)
  - [x] normalize uses of `getPDOConnection()` (ideally so every place uses just `getConnection()`) [[see caveat]](#getpdoconnection-removal)
  - [x] can we get rid of `getConnection()` and simply configure the database to be used through some constant or method from `DB`? [[see same caveat]](#getpdoconnection-removal)
  - [x] try to make PDOException handling a global behavior, instead of having `try..catch` inside _EVERY. METHOD._
    - [x] API - probably doable, as we can use the Slim error handler to catch that
    - [x] other places - may be doable with extra code being inserted via `auto_prepend_file`, but there's probably so many use cases. Moved to future tasks.
  - [x] remove all uses of `$MysqlHostname` and related variables (usually in some `settings.php`) [[see mysqli caveat]](#mysqli-replacement)
  - [x] double-check all `datacon.php` removals as it seems they were also used for including `settings.php` :roll_eyes:, which then had other things like `CRYPT_KEY`, timezone and `$ip_address`(?)
    - [x] remind/developer/manage
    - [x] others? [nope](https://github.com/dms-inc/ikase/commit/dc7d1a199473c82a1b5ee1339e101b51a6e4bead)
    - [ ] are those settings still of any use?
  - [x] and then, possibly also drop some (all?) uses of `datacon.php` - only possible after dropping `mysqli` uses as well, unless we adapt that connection to use information from `DB` [[see caveat]](#datacon.php-removal)
  - [x] run further checks
    - [x] related to the PHP 7 upgrade in general
    - [x] actually check if the application is still working, with some navigation in the browser?
- [x] to PHP 7.1
- [ ] to PHP 7.2
  - [ ] `mcrypt` is deprecated
    - [x] migrate to OpenSSL
    - [ ] actually test uses of the upgraded `encryptAES()` - I couldn't get a PHP 7 server with `mcrypt` running to check the previous results
- [x] to PHP 7.3
- [x] to PHP 7.4
- [x] PHPStorm inspections

### Caveats on the migration
A.K.A. *Things that couldn't be done properly at this time*.

#### Updating legacy or insecure SQL
I gave up on further replacements of legacy into more modern code, as there are way too many cases to be replaced by hand, and they're all different, what makes impossible to run a search & replace. This is what I wanted to do:

- replace `try..catch` uses with `runOrApiError()` (there are cases using `json_encode` and also hard coded JSON strings...)
- replace `INSERT/UPDATE/DELETE` calls with the cleaner methods (`DB::insert()`, for instance)
- parametrize all SQL calls - mostly a security issue; this must be done at some point, and thus moved into the first item in the next section, on [Important tasks](#other-important-tasks)
  - inside those, there are also more than 2400 uses of `addslashes()`. That's probably a poor man's protection against SQL injection - but it's not made for this, and it's *not* enough

#### MySQLi Replacement
Actually not exactly an issue, as MySQLi usage was such an edge case; however, a FIXME note was added as it was not possible to infer the intentions of the code at `api/tasks_pack.php`. This is also related to the standalone definitions of MySQL settings (e.g. `$MySqlHostname`).

#### datacon.php removal
This file was a bit tricky, as there were three versions of it, all including `settings.php` which then brings other configuration variables, unrelated to... data-connection. One these was safe to remove, and got replaced by `settings.php` directly. The other two were greatly simplified (one was even using `mysqli`), but still left in place as the new `check_zip` depended on them to define which database connection to use. Some `FIXME/TODO` notes were added in places further checks could be made to improve and cleanup.

Nonetheless, some variables defined in those files were further checked for usage:

- `$ip_address`: no use; removed - all places it's mentioned it's either instantiated there (with the same value from `$_SERVER`, though) or kinda considered a global, but in a non-functioning way (at a bunch of `manage/*/yahoo.php` files)
- `$crypt_key`: a couple of uses (not all places that mention it though, as some are including a `datacon.php` from elsewhere)
- `$host`: used; brought back, but now in `settings.php` (unrelated to data connections)
- `$db`: apparently it's mixed up between the database name and the connection object/resource.
  - Some places still concatenate it before table names, but as that's actually redundant (the connection is always to a single database, which would probably end up being the same as defined in `$db`), I left `FIXME` notes for further checking.
  - It seems at least one place is using `$db` as connection while it might not be defined (because it depends on the parent file having that defined...)
- `$link`: apparently used, but only in the enhanced `check_zip.php`. It could be further changed so we can simplify that?

#### getPDOConnection() removal

The initial idea was to merge `getConnection()` and `getPDOConnection()` definitions and uses, but this is more complicated than it seems, with the codebase state that I received. Thus, those two functions were left with their original names, while their contents were migrated to the more modern `DB::conn()` usage.  

The three ways that were used to connect to the database were being mixed up in a couple of files, with different parts connecting to different database instances. I'm not sure if this was accidental or intentional (as I see little sense in mixing PDO and `mysql_*` code in the same place). If it was intentional, some extra code must be written into `DB` to allow for force-changing the default connection used by the `run()` methods - as it seems to be, nonetheless, an edge use case.

Here are some files where I noticed that behavior:

- `remind/developer/manage/customers/update.php`: used to mix the `mysql` extension with PDO usage, with each one going to a different server. During Search & Replace, both uses became the same, but if the second part needs to connect to a different server, `DB` will need to allow for that first. A `FIXME` note was left on the affected part. That file also has a weird use of `$db` that doesn't match the PDO connection create soon after, so it might just be a bad case of copy-pasta.
- `iklock/manage/customers/update.php`: same as the previous file, but without the mixed uses of `$db` (this one used `$dbPDO` for the actual database connection, and didn't use a variable for the database name in  `mysql_query()`). This case seemed safe to remove the old `getPDOConnection()` usage.
- It's worth noticing that the third "version" of this copy-pasta is `manage/customers/update.php`, which is **not** affected by this messy mix: the `mysql_*` functions were commented out and already replaced by `getPDOConnection()`. Thus, if that was the main intention in the other files as well, it should be safe to simply fix that `$db` mix-up.
- a similar situation seems to happen with files that depend on `text_editor/ed/functions.php`'s `getPDOConnection()`: that seems to be the newer database connection, while old code used the `mysql_*` functions, but each one connects to a different database. Thus, when I tried to rename that into simply `getConnection()`, it started to be used by `DB::conn()` instead of the default (localhost) connection and it all got quite messed up in a bad way (this was the file that made me notice the issue... thanks to tests I wrote for it, yey!).

Nonetheless, the main objective was to make those `get*Connection()` actually a simple "configuration" function that defines which database to connect to, instead of actuallt using it to run database queries. Thus, they were all tagged as deprecated, and their only "allowed" use is inside `DB::conn()`. It was done this way because:

1. usually there's no other central file to each different applications, so having a single place to configure the database besides those function files would be a bit problematic;
2. there are still a _lot_ of places using `getConnection()` to run database queries, usually because they're complex queries that I couldn't migrate with S&R.

## Other important tasks

- [ ] **SECURITY RISK:** parameterize all calls, so they're protected from SQL injections. As there are way too many cases to check, it might be better to just change the queries that receive direct user input (i.e. coming from `$_GET`, `$_POST`, or `passed_var()`).
- [ ] check and resolve all `TODO` and `FIXME` notes
- [ ] update the Docker env with the missing extensions included in composer (`com_dotnet`, if it's even possible?)
- [ ] Provide some basic constants for more consistency throughout the project
  - [ ] replace all uses of the root path with `ROOT_PATH` (e.g. search for `wwwroot`)
    - [x] replace most common uses, that point to `C:\inetpub\wwwroot\iKase.org\`
    - [ ] there are some very weird checks of the `DOCUMENT_ROOT`. Some (all?) got marked with `FIXME` notes
    - [ ] there are some JS code that uses `iKase.website` (why is JS even aware of the server path???), and at least one PHP reference
    - [ ] there are references to `C:\inetpub\wwwroot\speech`, which is outside of the code tree
- [ ] De-duplications:
  - [x] `manage_session.php` files
    - [x] remove all duplicated uses besides `index.php`
    - [x] /api
    - [x] /iklock/api
    - [x] /remind/api
    - [x] ~~/remind/developer/api~~ doesn't really use the same session configurations
  - [x] `authorize()`
  - [x] `logout()`
  - [x] `/hash/{pwd}` & `encrypt()`
  - [x] `/killsleep` - what's this? sounds hackish
  - [x] `getCityState()`and probably some more boilerplate from the various `api/index.php`
    - [ ] *does it even work*? is it used?
  - [ ] simplify `legacy_session.php` use
  - [ ] `/login` & `/masterlogin` - hard to dedup as that's a long blob of code with a bunch of different stuff between APIs
  - [ ] check dependencies list above (a couple are duplicated as well)
  - [ ] why there are thousands of session openings with closures right after??
  - [ ] unify all `display_errors` and `error_reporting` settings
    - [x] API entrypoints
    - [ ] all other thousands of files
  - [ ] simplify all `try..catch` cases:
    - [ ] those inside Slim calls can be safely removed, as Slim is ready to catch any throwable and turn into the same error JSON that's used throughout the project - without the error-prone repetitiveness
    - [ ] when outside Slim, an extra file could be included in `auto_prepend_file` that would configure `JsonErrorRenderer` (or some middle-ground that would render an HTML error page in case that's what's requested) as an exception handler
  - [ ] there seems to be a thousand of repeated functions, like ` encrypt()` , `passed_var()`, `noSpecial()` etc...
  - [ ] ...and a LOT of functions inside the `connection.php` files?
  - [ ] ...nonetheless, definitely those three files can be merged into a single one:
    - `remind/manage/functions.php`
    - `remind/developer/manage/functions.php`
    - [ ] `text_editor/ed/functions.php`
  - [ ] move all those cache headers in the beginning of files to an external file, for more control
  - [ ] `cls_fileupload.php`
- [ ] there's a LOT of files with `die()` in the beginning - AKA probably not in use either. If those are partially-done work, that should be migrated to other branches as to not pollute the codebase - making it more confusing on what needs change, and potentially avoiding leaks or false errors.
- [ ] some of the `iklock/manage/*list_all` files have commented password encryption updates, some others don't...?
- [ ] simplify all those simple queries with loops that generate arrays of `<option>` (searching for `"<option` will probably find them all)

## Other useful tasks

- [-] Integrate the [PHP Debug Bar](http://phpdebugbar.com/docs/readme.html#installation)
  - [ ] installed, but not yet in used
