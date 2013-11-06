# Quick notes for installation

- Create a database in MySQL, in utf8_general_ci
- Create a directory on your hard drive, we'll assume it's at $SYMFONY
- Go in it  `cd $SYMFONY`
- Fork the repo and clone it in $SYMFONY: `git clone https://github.com/yourname/netrunnerdb`
- This creates a directory named netrunnerdb in $SYMFONY. Let's say DOCROOT=$SYMFONY/netrunnerdb. 
- Go into it.
- Install Composer: `curl -s http://getcomposer.org/installer | php`
- Install the vendor libs: `php composer.phar install` ; at the end of the install, you'll be asked for your database information
- Configure your local webserver with a DocumentRoot pointing to $DOCROOT (vhost probably)
- Create the tables: `php app/console doctrine:schema:update --force`
- If the above command fails, edit app/config/parameters.yml and try again
- Import the data from the latest `netrunnerdb-*.sql.gz` into MySQL
- Point your browser to `/web/app_dev.php` ; the page should load without card images
