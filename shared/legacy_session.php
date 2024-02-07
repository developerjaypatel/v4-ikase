<?php //intented to be a drop-in replacement for the old manage_session.php files used outside of the APIs
//TODO: originally, this file also required bootstrap.php; since that got moved into php.ini, the places where this file is required should either be replaced by the single command below, for simplicity, turn this file into a more robust DRY, doing other stuff all those places do (i.e. cache headers, although those probably also happen elsewhere)

\Api\Bootstrap::session();
