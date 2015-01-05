Installation instructions
=========================

Getting started
-------

To install, you'll need Composer and Bower to get the project's dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    sudo apt-get install npm
    npm install bower
    bower install

To build for the first time:

    cat build/database/init.sql build/database/create.sql build/database/update-issues.sql | mysql -u root -p

To set up web app configuration:

	cp config/env-config.php.example config/env-config.php

(And then edit the new file with your database settings)

To identify your Awooga environment, add this into your Apache vhost:

    SetEnv SLIM_MODE production

The usual environments are `local`, `staging`, `test` and `production`. Do not make a test site
available on the public internet, as it permits anonymous login.

To install cron/reboot tasks:

	sudo ./build/cron/install.sh

Reinstalling
------------

To delete the database and start again:

    cat build/database/destroy.sql | mysql -u root -p

General
-------

To run an update, do the following. Note that the cron will do it for you, so this is only if you
want to run it by hand.

    sudo console/create-mount-fs.sh && sudo -u awooga php console/update-repos.php

Testing
-------

Run this after the other database initialisation files to populate with test data:

    cat build/database/local/repositories_local.sql | mysql -u root -p

To run unit and browser tests:

	PATH=$PATH:`pwd`/bin ./phpunit
