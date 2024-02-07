iKase main project
==================

The grand PHP 7 upgrade
-----------------------
Heads up, fellow stranger! [There's a special doc file about all things shiny and new since we've upgraded to PHP 7](php7-upgrade.md). Otherwise, read below for general instructions on installation, testing, future tasks, long-term customizations on libraries, and more.

Installation
------------
### Production
Usual steps should be followed, with one extra thing: we need a specific `php.ini` setting, so we can include the
Composer Autoload in all requests. This is already done for the development Docker image, but the production server will
need this added as well.

1. Find the main `php.ini`, or the directory it reads. This can be achieved by running `php --info` or adding `phpinfo()`
to a script and executing it in the server. If you chose the latter, **DON'T FORGET TO REMOVE IT. THIS IS A SECURITY RISK.**
2. Add the `auto_prepend_file` setting, similarly to [what's in Docker](docker/php/user.ini), depending on the server
folder structure. **Don't just copy-paste it, pay attention to the current machine's file folders!**

Also, you should double-check if all required extensions are installed. Common missing extensions are `mysql` and `imagick` - `wincache` might be missing too, if it's enabled in IIS(?). You could dry-check that by running `composer check-platform-requirements`, but you would need to have composer installed - don't use Docker for this, as it's a separate environment anyway.

If you stumble upon BSODs (Blank Screen Of Death), try checking the server logs. The application may not show errors on the browser, but it's certainly logging it internally. Reasons it may be blanking at you:
- if the API couldn't start properly, it may not be able to issue pretty error messages;
- the production environment has restricted error reports, so sensitive information don't leak in case the user stumbles upon those errors.

### Development
1. Get the Docker environment running:
    1. [Install Docker](https://docs.docker.com/get-docker/)
    2. Build the image: `docker-compose build`
    3. Run it! `docker-compose up -d`
    > Currently, the database has no data, just the structure. That's suitable for Unit and some API tests, but we would need data factories or a data preset to run deeper API or Integration tests. 
2. Clone the project, if you haven't already
3. Create a GitHub Personal Access Token, so you can clone the private repository(ies) used:
    1. [Create one](https://github.com/settings/tokens) and copy it
    2. Create an `auth.json` with the following contents:
      ```json
      {
        "github-oauth": {
          "github.com": "« PASTE THE TOKEN HERE »"
        }
      }
      ```
   3. Install the dependencies: `docker-compose exec php composer install --prefer-dist`.  
   _You might also need to use `--ignore-platform-reqs` in case there are missing extensions (and please, add them at some point to the Docker container!)_


Testing
-------
1. Make sure the Docker images are up: `docker-compose up -d`
2. You can either execute them from PHPStorm (config files are probably already there), or with `docker-compose exec php composer test`

### Class naming convention
Some tests are deep inside folders, to further separate topics inside the same API. As Codeception doesn't report namespaces for test classes, we're back on using PEAR_Style_Classnames.

### Faker usage
There are two ways to use Faker around the project:

- Inside factories: use the Facade from FactoryMuffin (check some existing factory to see how). **Don't** use the plain
`faker()` inside factories, otherwise you'll get duplicated entries when using `haveMultiple()`!
- Anywhere else: use `faker()`, as defined in the tests' bootstrap file. FactoryMuffin's facade is verbosely weird to be used outside of factories ¯\_(ツ)_/¯

General repository tasks
------------------------
As many things needed to be carried before the code was actually placed on GitHub (otherwise I would be spending a couple of time rewriting history to push more than 500MB every day), and there were a LOT of small tasks, these were written simply to [another markdown file](improvements.md). 


Special "features"
------------------

### API functions' signature - our custom [Slim Route Strategy][slim4-strategy]
[Since Slim 3, route functions have changed their default signature][slim3-routes]. To cope with legacy code, "Route
Strategies" were created, so one could create a different way to call their own route functions. Due to the size of the
project stuck with the ancient Slim 1 code style, a non-standard Strategy was used.  
[Since Slim 4, functions cannot return strings and Request/Response objects got blander][slim4-response]. To cope with
that, some additional behaviors were included in the same Strategy.

Here's a summary of how our Route Strategy works:
- it's located at [shared/api/LegacyStrategy.php](shared/api/LegacyStrategy.php) 
- route parameters are passed just like 1.x: each one become a new argument in the function.
- the Request and Response objects are accessible as globals _(yeah, they're bad, but it wasn't really feasible to
search/replace the whole codebase to fix function arguments to include something that wasn't even really that used)_.
    - it's important to note that Request and Response objects are immutable. Thus, every time you call a mutating
      method, you'll receive a _new_ object!
- it's also possible to return a string or an array from the function - the array is going to be json_encoded. This
enables simple arrow functions to be used.

Example:

```php
use Fig\Http\Message\StatusCodeInterface as Status;
$app->get('/hello', fn() => 'hello there');
$app->get('/hello.json', fn() => ['msg' => 'hello there']);
$app->get('/letter/{id}[/{sort}]', function($id, $filter = null) {
    $letter = findLetter($id, $filter);
    $resp = $GLOBALS['response']->withStatus(Status::STATUS_CREATED);
    $resp->withBody()->write(json_encode($letter));
    return $resp;
});
```


Cheatsheet
----------
Anything like `«this»` should be replaced by the actual values.  
Anything inside `[brackets]` is optional (the brackets are not part of the command).

### Docker
Tip: create a shell alias of `dc=docker-compose`, so you avoid mistyping it as `docker-composer` and save 12 strokes :))

- (Re)build the images (currently only `php` is built): `docker-compose build`
- Check current image status: `docker-compose ps`
- Run something in an image: `docker-compose exec «image» «command»`
- Drop into the shell: `docker-compose exec php sh` or `docker-compose exec mysql bash` (the PHP image is Alpine-based, so there's no `bash`)

### Composer
All those commands should be executed through the Docker PHP image - e.g. `docker-compose exec php composer install`.
This avoids downloading dependencies locked to your own PHP version, and not to the project's version. For the same
reason, if you find some issue of missing dependency or extension, try to fix the Docker environment instead of using
`--ignore-platform-reqs`.

- install all dependencies (or if a new one is added directly to `composer.json`): `composer install`
- update the local dependencies and lockfile with what is allowed by version constraints: `composer update`
- add a new package: `composer require «vendor»/«pkg»[:«version»]`
- run tests: `composer test` (this is a custom command)

### Tests
The project uses Codeception tests. You can either run them from:
- PHPStorm: that's probably already configured, as PHPStorm shareable settings are in the repository;
- CLI: `docker-compose exec php composer test`, or `composer test` after `docker-compose exec php sh`.

[slim4-strategy]: http://www.slimframework.com/docs/v4/objects/routing.html#route-strategies
[slim3-routes]: http://www.slimframework.com/docs/v3/start/upgrade.html#new-route-function-signature
[slim4-response]: https://github.com/slimphp/Slim/issues/2940
