-- MySQL dump 10.13  Distrib 5.5.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: awooga
-- ------------------------------------------------------
-- Server version	5.5.37-0ubuntu0.13.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `report`
--

LOCK TABLES `report` WRITE;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
INSERT INTO `report` (`id`, `repository_id`, `user_id`, `title`, `description`, `description_html`, `author_notified_at`, `is_enabled`, `created_at`, `updated_at`) VALUES (1,1,NULL,'How to Display MySQL Table Data Tutorial','A number of security flaws, and so many syntax issues it wouldn\'t work at all. The author [has promised to fix it](https://twitter.com/ilovephp/status/523945131800817664).','<p>A number of security flaws, and so many syntax issues it wouldn\'t work at all. The author <a href=\"https://twitter.com/ilovephp/status/523945131800817664\">has promised to fix it</a>.</p>\n','2014-10-19',1,NULL,NULL),(2,1,NULL,'Create secure login script in PHP','Tweeted to author [about library and parameterisation](https://twitter.com/ilovephp/status/523163293041840129), and [about hashing](https://twitter.com/ilovephp/status/523163435878854656), but received no response.','<p>Tweeted to author <a href=\"https://twitter.com/ilovephp/status/523163293041840129\">about library and parameterisation</a>, and <a href=\"https://twitter.com/ilovephp/status/523163435878854656\">about hashing</a>, but received no response.</p>\n','2014-10-17',1,NULL,NULL),(3,1,NULL,'Implement MySQL-based transactions with a new set of PHP extensions','Uses modern MySQLi library, but no parameterisation - vulnerable to SQL injections. [Tweeted to publisher](https://twitter.com/ilovephp/status/528134535439872000) to no avail.','<p>Uses modern MySQLi library, but no parameterisation - vulnerable to SQL injections. <a href=\"https://twitter.com/ilovephp/status/528134535439872000\">Tweeted to publisher</a> to no avail.</p>\n','2014-10-31',1,NULL,NULL),(4,1,NULL,'Tutorial Make a Simple Website E-Commerce with PHP MySql and Bootstrap','The problem here is the [zipfile](https://www.dropbox.com/s/lt4ng1pm5vyb3y0/shop.zip), which contains SQL injection flaws. I\'ve [let the author know](https://twitter.com/ilovephp/status/523919390983868416), to no avail.','<p>The problem here is the <a href=\"https://www.dropbox.com/s/lt4ng1pm5vyb3y0/shop.zip\">zipfile</a>, which contains SQL injection flaws. I\'ve <a href=\"https://twitter.com/ilovephp/status/523919390983868416\">let the author know</a>, to no avail.</p>\n','2014-10-18',1,NULL,NULL),(5,1,NULL,'Youtube like rating script jquery php','It\'s worth disabling JavaScript for this site - the whole page uses JavaScript to redirect to an advertiser\'s site. PHP code features variable as well as SQL injection. Have [contacted the author](https://twitter.com/ilovephp/status/525794166803292160), and [the author has undertaken to fix it](https://twitter.com/amitspatil/status/540083644753129473).','<p>It\'s worth disabling JavaScript for this site - the whole page uses JavaScript to redirect to an advertiser\'s site. PHP code features variable as well as SQL injection. Have <a href=\"https://twitter.com/ilovephp/status/525794166803292160\">contacted the author</a>, and <a href=\"https://twitter.com/amitspatil/status/540083644753129473\">the author has undertaken to fix it</a>.</p>\n','2014-10-24',1,NULL,NULL),(6,1,NULL,'Creating a Login System in PHP (Tutorial)','[Tweeted to author](https://twitter.com/ilovephp/status/522789868301479937), received no response.','<p><a href=\"https://twitter.com/ilovephp/status/522789868301479937\">Tweeted to author</a>, received no response.</p>\n','2014-10-31',1,NULL,NULL),(7,1,NULL,'Develop a Complete Android Login Registration System with PHP, MySQL','The usual SQL injection flaws in this one, the [author has been notified](https://twitter.com/ilovephp/status/524685931404881920). Also, the password hashing isn\'t strong enough. Looks like the login can be bypassed by changing the target user\'s password','<p>The usual SQL injection flaws in this one, the <a href=\"https://twitter.com/ilovephp/status/524685931404881920\">author has been notified</a>. Also, the password hashing isn\'t strong enough. Looks like the login can be bypassed by changing the target user\'s password</p>\n','2014-10-21',1,NULL,NULL),(8,1,NULL,'PHP and MySQL Tutorial','A variety of issues with the chapters here. Some seem to be proofed against SQL injection, but nevertheless need parameterisation, others (e.g. Deleting Data from MySQL Database, Updating Data into MySQL Database) contain straightforward SQL injection vulns. Have [tweeted to author](https://twitter.com/ilovephp/status/523546917335478272), recceived no reply.','<p>A variety of issues with the chapters here. Some seem to be proofed against SQL injection, but nevertheless need parameterisation, others (e.g. Deleting Data from MySQL Database, Updating Data into MySQL Database) contain straightforward SQL injection vulns. Have <a href=\"https://twitter.com/ilovephp/status/523546917335478272\">tweeted to author</a>, recceived no reply.</p>\n','2014-10-18',1,NULL,NULL),(9,1,NULL,'Simple registration form in PHP and MYSQL','Have [contacted author](https://twitter.com/ilovephp/status/525415879463686144) about SQL injection, received no response. Also features plain-text passwords.','<p>Have <a href=\"https://twitter.com/ilovephp/status/525415879463686144\">contacted author</a> about SQL injection, received no response. Also features plain-text passwords.</p>\n','2014-10-23',1,NULL,NULL),(10,1,NULL,'Android PHP/MYSQL Tutorial','SQL injection issues, despite using mysqli. Also incorrectly advocates for the use of plain text in a password storage system. Have [contacted the author](https://twitter.com/ilovephp/status/540182898960523264) to ask for improvements.','<p>SQL injection issues, despite using mysqli. Also incorrectly advocates for the use of plain text in a password storage system. Have <a href=\"https://twitter.com/ilovephp/status/540182898960523264\">contacted the author</a> to ask for improvements.</p>\n',NULL,1,NULL,NULL),(11,1,NULL,'PHP CRUD with Search and Pagination','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(12,1,NULL,'PHP CRUD with Search and Pagination using jQuery AJAX','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(13,1,NULL,'Simple PHP Shopping Cart','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(14,1,NULL,'Live Username Availability Check using PHP and jQuery AJAX','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(15,1,NULL,'jQuery AJAX Autocomplete â€“ Country Example','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(16,1,NULL,'Facebook Style Like Unlike using PHP jQuery','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(17,1,NULL,'Tutorial Menu AJAX Add Edit Delete Records in Database using PHP and jQuery','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(18,1,NULL,'Tutorial Menu Using jqGrid Control with PHP','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(19,1,NULL,'Dynamic Star Rating with PHP and jQuery','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(20,1,NULL,'Dynamic Content Load using jQuery AJAX','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(21,1,NULL,'PHP Voting System with jQuery AJAX','A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.','<p>A site with a large number of vulnerable scripts, including many that are live on the author\'s own server.</p>\n',NULL,1,NULL,NULL),(22,1,NULL,'Demo Facebook like Button Application Using PHP, MySQL, jQuery and Ajax','Uses legacy library, similar SQL injection vulns to other MySQL tutorials on this domain.','<p>Uses legacy library, similar SQL injection vulns to other MySQL tutorials on this domain.</p>\n',NULL,1,NULL,NULL),(23,1,NULL,'Instant Search With Pagination in PHP, MySQL, jQuery and Ajax','Two similar pagination tutorials, both with security vulnerabilities','<p>Two similar pagination tutorials, both with security vulnerabilities</p>\n',NULL,1,NULL,NULL),(24,NULL,4,'Responsive Quiz Application Using PHP, MySQL, jQuery, Ajax and Twitter Bootstrap','Uses legacy library. Several SQL injection vulnerabilities here.','<p>Uses legacy library. Several SQL injection vulnerabilities here.</p>\n',NULL,1,NULL,NULL),(25,1,NULL,'jQuery Autocomplete Mutiple Fields Using jQuery, Ajax, PHP and MySQL','Two versions of this tutorial. Have [contacted the author](https://twitter.com/ilovephp/status/543363764079583232) to let them know about the SQL injection issue in both.','<p>Two versions of this tutorial. Have <a href=\"https://twitter.com/ilovephp/status/543363764079583232\">contacted the author</a> to let them know about the SQL injection issue in both.</p>\n','2014-12-12',1,'2015-01-10 18:00:00',NULL),(26,NULL,2,'Simple Login with CodeIgniter in PHP','A CodeIgniter tutorial that uses MD5 to hash passwords, with no salt.','<p>A CodeIgniter tutorial that uses MD5 to hash passwords, with no salt.</p>\n',NULL,1,'2015-01-11 11:22:33','2015-01-11 14:46:01');
/*!40000 ALTER TABLE `report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `report_issue`
--

LOCK TABLES `report_issue` WRITE;
/*!40000 ALTER TABLE `report_issue` DISABLE KEYS */;
INSERT INTO `report_issue` (`id`, `report_id`, `description`, `description_html`, `issue_id`, `resolved_at`) VALUES (5657,22,NULL,NULL,2,NULL),(5658,22,NULL,NULL,5,NULL),(5659,4,NULL,NULL,2,NULL),(5660,13,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5661,13,NULL,NULL,5,NULL),(5662,14,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5663,14,NULL,NULL,5,NULL),(5664,23,NULL,NULL,2,NULL),(5665,23,NULL,NULL,5,NULL),(5666,15,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5667,15,NULL,NULL,5,NULL),(5668,16,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5669,16,NULL,NULL,5,NULL),(5670,2,NULL,NULL,3,NULL),(5671,2,NULL,NULL,6,NULL),(5672,2,NULL,NULL,5,NULL),(5673,11,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5674,11,NULL,NULL,5,NULL),(5675,17,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5676,17,NULL,NULL,5,NULL),(5677,12,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5678,12,NULL,NULL,5,NULL),(5679,18,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5680,18,NULL,NULL,5,NULL),(5681,24,NULL,NULL,2,NULL),(5682,24,NULL,NULL,5,NULL),(5683,19,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5684,19,NULL,NULL,5,NULL),(5685,20,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5686,20,NULL,NULL,5,NULL),(5687,6,NULL,NULL,6,NULL),(5688,6,NULL,NULL,3,NULL),(5689,1,NULL,NULL,2,NULL),(5690,1,NULL,NULL,5,NULL),(5691,5,'The `$item` variable can be used in a POST op to inject arbitrary SQL into a database query','<p>The <code>$item</code> variable can be used in a POST op to inject arbitrary SQL into a database query</p>\n',2,NULL),(5692,5,NULL,NULL,5,NULL),(5693,5,'The use of `extract()` to create variables from unfiltered user input is risky, since it can have malicious uses','<p>The use of <code>extract()</code> to create variables from unfiltered user input is risky, since it can have malicious uses</p>\n',7,NULL),(5694,25,'The MySQLi library is in use, so this just needs modifying to parameterised queries','<p>The MySQLi library is in use, so this just needs modifying to parameterised queries</p>\n',2,NULL),(5695,8,NULL,NULL,2,NULL),(5696,8,NULL,NULL,6,NULL),(5697,8,NULL,NULL,5,NULL),(5698,10,NULL,NULL,2,NULL),(5699,10,NULL,NULL,6,NULL),(5700,10,NULL,NULL,3,NULL),(5701,7,NULL,NULL,2,NULL),(5702,7,NULL,NULL,5,NULL),(5703,7,'SHA1/base64/salt home-made algorithm not a substitute for password_hash().','<p>SHA1/base64/salt home-made algorithm not a substitute for password_hash().</p>\n',4,NULL),(5704,21,'This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the [author cites parameterisation as a benefit of MySQLi](http://phppot.com/php/mysql-vs-mysqli-in-php/) elsewhere on the site.','<p>This site contains a large number of SQL injections, all or mostly involving the legacy mysql library. Interestingly the <a href=\"http://phppot.com/php/mysql-vs-mysqli-in-php/\">author cites parameterisation as a benefit of MySQLi</a> elsewhere on the site.</p>\n',2,NULL),(5705,21,NULL,NULL,5,NULL),(5706,9,NULL,NULL,2,NULL),(5707,9,NULL,NULL,5,NULL),(5708,9,NULL,NULL,3,NULL),(5709,3,NULL,NULL,2,NULL),(5710,26,NULL,NULL,4,NULL);
/*!40000 ALTER TABLE `report_issue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `repository`
--

LOCK TABLES `repository` WRITE;
/*!40000 ALTER TABLE `repository` DISABLE KEYS */;
INSERT INTO `repository` (`id`, `url`, `mount_path`, `is_enabled`, `created_at`, `due_at`, `updated_at`) VALUES (1,'file:///home/jon/Development/Personal/Awooga/reports','1d01a3262bf6f264b7c66d3884e7227e021ffbc3',1,'2014-11-15 21:33:00','2014-12-14 03:40:02',NULL);
/*!40000 ALTER TABLE `repository` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `repository_log`
--

LOCK TABLES `repository_log` WRITE;
/*!40000 ALTER TABLE `repository_log` DISABLE KEYS */;
INSERT INTO `repository_log` (`id`, `repository_id`, `run_id`, `log_type`, `message`, `created_at`, `log_level`) VALUES (668,1,270,'fetch',NULL,'2014-12-13 00:10:01','success'),(669,1,270,'move',NULL,'2014-12-13 00:10:01','success'),(670,1,270,'scan',NULL,'2014-12-13 00:10:02','success'),(671,1,270,'resched',NULL,'2014-12-13 00:10:02','success'),(672,1,274,'fetch',NULL,'2014-12-13 23:40:01','success'),(673,1,274,'move',NULL,'2014-12-13 23:40:01','success'),(674,1,274,'scan',NULL,'2014-12-13 23:40:02','success'),(675,1,274,'resched',NULL,'2014-12-13 23:40:02','success');
/*!40000 ALTER TABLE `repository_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `resource_url`
--

LOCK TABLES `resource_url` WRITE;
/*!40000 ALTER TABLE `resource_url` DISABLE KEYS */;
INSERT INTO `resource_url` (`id`, `report_id`, `url`) VALUES (2711,22,'http://www.smarttutorials.net/demo-facebook-like-button-application-using-php-mysql-jquery-and-ajax/'),(2712,4,'http://betterbusinessforever.com/?p=354'),(2713,13,'http://phppot.com/php/simple-php-shopping-cart/'),(2714,14,'http://phppot.com/jquery/live-username-availability-check-using-php-and-jquery-ajax/'),(2715,23,'http://www.smarttutorials.net/instant-search-with-pagination-in-php-mysql-jquery-and-ajax/'),(2716,23,'http://www.smarttutorials.net/pagination-previous-next-first-and-last-button-using-jqgrid-php-mysql-jquery-and-ajax/'),(2717,15,'http://phppot.com/jquery/jquery-ajax-autocomplete-country-example/'),(2718,16,'http://phppot.com/jquery/facebook-style-like-unlike-using-php-jquery/'),(2719,2,'http://www.webinfopedia.com/php-secure-login-script.html'),(2720,11,'http://phppot.com/php/php-crud-with-search-and-pagination/'),(2721,17,'http://phppot.com/jquery/ajax-add-edit-delete-records-in-database-using-php-and-jquery/'),(2722,12,'http://phppot.com/php/php-crud-with-search-and-pagination-using-jquery-ajax/'),(2723,18,'http://phppot.com/jquery/using-jqgrid-control-with-php/'),(2724,24,'http://www.smarttutorials.net/responsive-quiz-application-using-php-mysql-jquery-ajax-and-twitter-bootstrap/'),(2725,19,'http://phppot.com/jquery/dynamic-star-rating-with-php-and-jquery/'),(2726,20,'http://phppot.com/jquery/dynamic-content-load-using-jquery-ajax/'),(2727,6,'http://vimeo.com/108934852'),(2728,6,'http://www.onlinetuting.com/create-login-script-in-php/'),(2729,1,'http://www.siteground.com/tutorials/php-mysql/display_table_data.htm'),(2730,5,'http://www.amitpatil.me/youtube-like-rating-script-jquery-php/'),(2731,25,'http://www.smarttutorials.net/jquery-autocomplete-multiple-fields-using-ajax-php-mysql-example/'),(2732,25,'http://www.smarttutorials.net/jquery-autocomplete-search-using-php-mysql-and-ajax/'),(2733,8,'http://www.tutorialspoint.com/php/php_and_mysql.htm'),(2734,10,'http://www.tutorialspoint.com/android/android_php_mysql.htm'),(2735,7,'http://www.learn2crack.com/2013/08/develop-android-login-registration-with-php-mysql.html/4'),(2736,21,'http://phppot.com/jquery/php-voting-system-with-jquery-ajax/'),(2737,9,'http://www.webinfopedia.com/registration-form-in-php.html'),(2738,3,'http://www.techrepublic.com/article/implement-mysql-based-transactions-with-a-new-set-of-php-extensions/'),(2739,26,'http://www.iluv2code.com/login-with-codeigniter-php.html');
/*!40000 ALTER TABLE `resource_url` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `run`
--

LOCK TABLES `run` WRITE;
/*!40000 ALTER TABLE `run` DISABLE KEYS */;
INSERT INTO `run` (`id`, `created_at`) VALUES (270,'2014-12-13 00:10:01'),(274,'2014-12-13 23:40:01');
/*!40000 ALTER TABLE `run` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `username`, `access_level`) VALUES
(1,'halfer','reporter'),(2,'https://github.com/halfer','reporter'),(4,'testuser','reporter');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-12-15  0:08:20
