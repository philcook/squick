WP CLI Squick
====================

Install WordPress with plugins with one command.

Installation
============

 * https://github.com/wp-cli/wp-cli/wiki/Community-Packages#wiki-installing-a-package-without-composer

Usage
=====

```bash
# create a standard wp-cli.local.yml and/or wp-cli.yml in your site directory then ...
wp squick install
```

Example output

```bash
$ wp squick install
Installing to /Users/username/Sites/squick/ ...
Downloading WordPress 3.8.1 (en_US)...
Success: WordPress downloaded.
Success: Created 'squick' database.
Success: Generated wp-config.php file.
Success: WordPress installed successfully.
```

See [wp-cli.local.yml](https://github.com/philcook/squick/blob/master/wp-cli.local.yml)
for default values.
