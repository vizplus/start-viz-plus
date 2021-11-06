# start.VIZ.plus

Here is the open source code of the [start.vis.plus](https://start.viz.plus/) service, which is a service for registering a zero account in VIZ and activating pre-generated codes at a given price in dollar equivalent.

To access the administration panel, you must log in using the path /login/, after which you will be redirected to the control panel.

## Scripts

- config.example.php stores parameters for access to the administrative script, database access (MySQL), node address for JSON-RPC requests, secret key for the Google Recaptcha API;
- autoloader.php - the helper file for initializing the state of other scripts;
- index.php initializes path parsing and connects the requested module, then fills in the template and returns the execution result to the user;
- module/prepare.php contains preset values and starts session initialization;
- module/ajax.php provides the ability to request whether the login is free and use the code;
- module/functions.php contains methods for generating raw transactions and signing them using secp256k1-php;
- module/index.php contains both the user part and the administrative part (allows you to log in to manage codes, set the price in dollar equivalent for 1 viz).

## Related files

- tables.sql contains SQL commands for creating tables related to storing data available for search and sorting;
- js files from dependencies;
- config.example.php needs to be renamed to config.php;
- nginx.example.conf is example of nginx configuration for correct redirection of all requests of the /path/to/page/ form to the root index.php .

## Dependencies

- MySQL;
- nginx;
- php;
- Google Recaptcha;
- jsonrpc VIZ node;
- jQuery;
- viz-js-lib;
- [secp256k1-php](https://github.com/On1x/secp256k1-php);

