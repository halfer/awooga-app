Installation instructions
-------------

To build for the first time:

    cat build/database/init.sql build/database/create.sql build/database/update-issues.sql | mysql -u root -p

Also run this to populate with test data:

    cat build/database/local/repositories_local.sql | mysql -u root -p

To set up web app configuration:

	cp config/env-config.php.example config/env-config.php

(And then edit the new file with your database settings)

To delete:

    cat build/database/destroy.sql | mysql -u root -p

To run an update:

    sudo console/create-mount-fs.sh && sudo -u awooga php console/update-repos.php

To install cron/reboot tasks:

	sudo ./build/cron/install.sh

To run unit and browser tests:

	PATH=$PATH:`pwd`/bin ./phpunit
