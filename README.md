Installation instructions
-------------

To build for the first time:

    cat build/init.sql build/create.sql build/update-issues.sql | mysql -u root -p

Also run this to populate with test data:

    cat build/local/repositories_local.sql | mysql -u root -p

To delete:

    cat build/destroy.sql | mysql -u root -p

To run an update:

    sudo console/create-mount-fs.sh && sudo -u awooga php console/update-repos.php
