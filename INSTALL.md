Fairness Time & Attendance
==========================

INSTALLATION INSTRUCTIONS
-------------------------

1. Confirm that your system meets the Fairness minimum requirements.
  - PHP v5.x or greater
	- MySQL v5.0+ or PostgreSQL v8.2+ (PostgreSQL is highly recommended)

2. Locate your webroot directory on your web server. This is the directory on your web server where publicly accessilbe files are made available by your
web server. Common locations include:

	/var/www/html/ (Linux/Apache)
	C:\Inetpub\wwwroot\ (Windows/IIS)
	C:\Program Files\Apache Group\Apache\htdocs\ (Windows/Apache)
	/Library/Web server/Documents/ (MaxOS X/Apache)

3. Copy the all the files of fairness in that directory.

4. Rename fairness.ini.php-example_(linux|windows) to fairness.ini.php

5. Edit fairness.ini.php and confirm that all paths are correct. The installer will create and configure the database for you, as well as modify other non-path settings for you.

6. Point your web browser to: http://<web server address>/<fairness directory>/interface/install/install.php ie: http://localhost/fairness/interface/install/install.php

7. Follow instructions

UPGRADE INSTRUCTIONS

1. *IMPORTANT* Create a backup of your current installation, including your Fairness database.

2. *VERY IMPORTANT* No really, create a backup of all your Fairness data including your
   fairness.ini.php file, as it contains a cryptographic salt that if you lose you will
   not be able to login to Fairness or access encrypted data ever again.

   **BE SURE TO BACKUP YOUR TimeTrex DATABASE AND YOUR fairness.ini.php FILE!**

3. Copy the current version of Fairness over the top of your current installation.

4. Edit fairness.ini.php in your new Fairness directory and set: installer_enabled = TRUE

5. Point your web browser to: http://<web server address>/<fairness directory>/interface/install/install.php ie: http://localhost/fairness/interface/install/install.php

6. Follow instructions, Fairness will automatically upgrade your database tables as necessary.