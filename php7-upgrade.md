# Upgrade to PHP7: overview of changes

## To-dos

There are still a couple of tasks that _should_ be done to keep the project in a more maintainable level. You'll notice that, in many parts of this document, I mention partial changes; that's because I did what was most essential to the current task, and moved on. What's left is shown at the [Improvements][improvements.md] file - for instance, Slim got moved into Composer, but other dependencies didn't need an upgrade at this moment, so they were left the way they are.

## Dependencies

### Migration from Slim 1.x to 4

Small changes that were needed:

- Slim got namespaces
- request handling changed; there were a LOT of request declarations unused, so I removed all of them to avoid unexpected side-effects if someone ever tried to use it
- middleware usage changed. To make this migration easier and DRY code a bit, I started using route groups everywhere - so the final URIs are less error-prone and we don't need to repeat the middleware that much throughout the routes declaration.

#### Route changes

This was a necessary change, as earlier Slim versions didn't support PHP 7. Most of the these were signature changes and some other "code movements", but one important change is how Slim deals with route arguments. As this would break most of our current code, there's a custom _Route Strategy_ to allow us to keep the old behavior alive.

This is not really some "dirty workaround to keep legacy code alive", as it may seem. The Slim changes were mainly to keep the framework current with the latest standards in PHP - at 2016 the [PSR-7 HTTP Message Interface](https://www.php-fig.org/psr/psr-7/) was released, and frameworks should adapt to it. We don't really need to adhere to it, but if we ever need to replace the underlying framework, it would probably be better to do so. Meanwhile... we're fine with the project working the way it is, with Slim allowing us to keep the legacy format.

#### Error rendering

The code originally returned HTML error pages in any case. I created a proper error renderer for Slim that will generate an adequate JSON for API calls requesting JSON responses (useful for tests, but it also helps in case you want to give friendly messages to users when there's an unexpected API error in the frontend). This was also encapsulated in some common cases of database use, to be described in the [Database](#database-usage) section.

###  Composer

I moved some (repeated?) dependencies from random folders into [Composer](https://getcomposer.org/), which is the standard on PHP dependency management. It's centralized and helps keeping up to date with dependency versions and locking to an exact version.

There is a quick cheat sheet of Composer commands in the project's [README](README.md) file.

## General code organization

### DRY *some* stuff ([*Don't Repeat Yourself* vs WET, *Write Everything Twice*]([https://en.wikipedia.org/wiki/Don%27t_repeat_yourself](https://en.wikipedia.org/wiki/Don't_repeat_yourself)))

There's a LOT. A LOT of repeated code throughout the project. While cleaning up the code wasn't my main task, the main benefit here was to weed out useless time on replacing, checking and testing duplicated code, or unused files. At some point I noticed actual cleaning would take forever, so I moved on some more important tasks, but... there were still a lot of stuff I kept seeing over and over again, but oh well. In anyway, I noted everything else that was WET at `improvements.md`.

Initially, I started reorganizing some of that. It was important to lay some makeshift foundations, as sooner or later I would have to change code in almost the entire project - and past are the days of random includes or direct script access on the web server.

There's also a lot of code that got deleted. There were a bunch of functions that were probably old API endpoints that were not in use anymore, so I removed them. There were also a bunch of `xyz_test.php`, `abc_copy_2018.php` that I removed.

#### General initialization code

Thus, the first things I included were a `bootstrap` and a `constants` files, so they run the most basic and common code, and include the relevant constants. 

Any, ANY kind of string or number (*Magic Numbers are Bad*:tm:) that is fixed and project wide should become a constant. It's important to keep them all in a single file, so it becomes sort of a "configuration" central. Things that went into the `constants` file as of now:

- Paths. One thing that was definitely missing was the definition of the root path of the project, so in case your infrastructure changes, you don't have to search and replace the entire codebase just to change a directory. This was specifically useful for the Docker change, so I search+replaced all windows paths with a relative constant, so the project works in any folder it's placed - as long as the HTTP server knows where it is, of course. I also included some other common folders.
- some time constants: it's more human-readable to read `6 * HOUR` instead of `6 * 60 * 60`
- environment constants: sometimes it's important to understand if we're running in dev, test or prod, so you can, for instance, switch to a test database or disable sending emails to clients.

The `bootstrap` file is meant to be the _only_ include that's mandatory in all files. It will bring in Composer, the constants, and should configure the common settings. Currently it only configures error reporting and timezone, but I've already noticed a _lot_ of files include caching headers - that should go in the bootstrap file as well, or at least into a secondary, optional file.

#### Shared code between sub-systems

There's a couple of new stuff under `/shared`. A couple of code repeated throughout the Slim code got moved to `/shared/Api`, together with the new code I had to write to accommodate the framework upgrades.

WET code from legacy APIs (i.e. directly-accessed PHP files that spit out JSON or pipe-separated text) was centralized to `/shared/legacy_api`. Repeated functions also went to `/shared`, together with some classes and a `legacy_session.php` - this one encapsulates all the repeated session code that got into `\Api\Boostrap::session()`, but intended to be a drop-in replacement for the legacy files, in place of `manage_session.php`.

Last but not least, there are also some Database classes that will be described in the next section

## Database usage

### Class-based database access

The legacy code had some classes that were used as intermediates to the database - usually called "models" in the MVC pattern. Those plain classes now inherit from `DbAccess`, which gives them some basic methods to run queries. There are still other cases to be migrated but, for now, `Note` and `Zipcode` use it (both migrated to `/shared`).

### Direct database access

This is where most of the database usage of the project happens. A great number of files had direct usage of `mysql_*` functions, as well as PDO code (with WET boilerplate). The traditional function calls were simplified and encapsulated into `DB` methods, as explained below:

#### Connection

This used to happen through several WET `getConnection()` functions. Ideally, this behavior should be migrated to the `DB` class as well, but one thing at a time... Thus, Those functions now are simply a shell for `DB::conn()`, which stores separate instances for each database available in the system.

This doesn't allow multiple connections to the same database to be accidentally created in the same request, while centralizing all database settings (at `DB::dbParams()`). If direct access to the PDO object is needed, one should either call `DB::conn()` from the outside, or `DB::connection()` from the inside. The `DB` class must _not_ define which connection to run any operation, it must leave this to the `getConnection()` definition, or use the default. In the future, `getConnection()` shall be removed and there should be no way to get the PDO object from outside the class - so it encapsulates all database access with an opaque API.

#### Running queries

Now there are two ways to execute SQL:

- the plain `DB::run()` method, that will return a `PDOStatement` (or its counterparts `runOrDie()` and `runOrApiError()`, that simplify the common logic to die with a string or JSON error). While not marked as deprecated, **this use is discouraged** as it still allows SQL Injections, common typos, and no-WHERE queries.

- or use more high-level methods for common operations, such as `DB::insert()`, `update()` and `delete()`. Those methods reduce the boilerplate of writing SQL for those common operations that, most of the time, have a clear and simple structure, while transparently parameterizing queries, being more secure in many ways.

  They also have `...orDie()` counterparts, but there's no `orApiError()` yet - doesn't seem to be useful anywhere yet. 

## Others

### How versioning changes what stays in the repository

One of the things that versioning helps is to bring back old code, or to separate half-made features from production-ready code. Also, user-land files do not belong in the repository, as they're temporary and user-dependent - we don't version the database contents (we backup them), so user uploads shouldn't be either. Similar thought is used with log files - they're system-dependent and there's no reason at all to move them into the repository.

Thus, while I initially got most of the project files via FTP (skipping folders that were blatantly user files), I ignored everything that seemed to be irrelevant to the actual project execution. Those user-upload folders are still in the project tree, but with [`.gitignore`](https://www.pluralsight.com/guides/how-to-use-gitignore-file) files inside of them - this way they're still created when the project is cloned, but their contents will never go into versioning.

A lot of "casual debug" code also got removed. As we're versioned now, there's no need to keep commented-out code in the codebase, as that functions either to pollute code (hindering code understanding) or to make it easier to slip debug code into production (definitely a Bad Idea:tm:). We all need some `die()` and `var_dump()` calls in our daily debug sessions, but these should _never_ be included in the committed code - not even committed, as it's expected that, once you commit something, it has been previously tested and is considered to be working.

### Docker

Docker is the thin brother of VirtualBox. It probably runs fine on Windows, but the main tech was born on Linux ages ago, and it was just a way to isolate processes. Then, some years ago, someone had the idea to organize that into a neat package that allows you to install a whole operating system without actually virtualizing all the hardware. The end result is that you can include in the project a couple of text files that instruct Docker how to recreate the exact environment your project needs to get running :)

>   It was originally made for me to get the project running locally, in my Linux computer, so not everything is working - as some parts of the project depend on Windows stuff. However, as that's just a part of the project, it's completely fine for developing most of it, test, do continuous integration in the future, etc.

We use [Docker Compose](https://docs.docker.com/compose/) (do not confuse it with PHP's Composer!) to organize the containers our application needs. There's one container for PHP (called `php`) and one for MySQL (called `db`), so they behave like separate servers, which is the usual setup, and communicate through the internal network that Docker creates - each container can be recognized inside that network by their name as host, so the PHP code can connect to `db:3306` to talk to the MySQL service, for instance.

I'm not sure how [Docker works on Windows](https://docs.docker.com/docker-for-windows/install/), but once you get it installed, you're probably able to execute it from the command line using something like `docker-compose up -d`. The server can then be accessed at `127.0.0.1:45273`, and to drop into its shell, run `docker-compose exec php sh`.

There is a quick cheat sheet of Docker commands in the project's [README](README.md) file.

### Tests

I added the [Codeception](https://codeception.com) library to run tests that could ensure my changes would keep the APIs working the way they should. To run these, you can use `docker-compose exec php composer test`. It's also possible to configure PHPStorm to run them - it should be working out of the box, I guess. It's also possible to create [Acceptance tests](https://codeception.com/docs/03-AcceptanceTests) with it - they're slower, but still useful to make sure the interface behaves as expected, in a consistent and automatable way.

They're kept at the `/tests` folder; there are two main folders:

- `api`: holds any API test. It's a bit different from usual unit tests, as they deal with an HTTP server and test its responses instead of actual function calls. It depends on the docker container running, which spins up PHP's built-in server. [Codeception API Testing docs](https://codeception.com/docs/10-APITesting)
- `unit`: those are common PHPUnit tests, with a Codeception twist - you can use some modules from the API tests, like the DB module, so that's quite handy and keeps all tests with sort of the same structure. [Codeception Unit Testing docs](https://codeception.com/docs/05-UnitTests)

Then, there's a couple of helper folders:

- `_data` is currently empty, but is meant to include raw files to be imported in tests, such as custom database dumps or files that tests use to check against results they receive.
- `_output` has its contents ignored, and receives failure output from tests, so you can do fine debugging on what APIs calls responded.
- `_support` keeps extra classes, like custom test modules. This folder is autoloaded by Codeception.

#### Tests data - Faker

Last but not least, the tests use the [Faker](https://github.com/fzaninotto/Faker) library to randomize test data. This makes tests less predictable, which at first may seem bad, as it leads to flaky tests, but that actually means the code is problematic for some unknown case. If a test fails, check the inputs used and fix it!

In the future, some sort of data factory (Codeception also has one) should be used, once we start writing "fixed" versions of information for some tables. Another option is using data dumps that Codeception can reload before every test, but that's usually too much stuff to run for big databases.