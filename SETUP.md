# Installing/Configuring Apache/PHP/MySQL on Windows 7/8/10

## Apache Webserver 2.4.x for Windows(httpd)
1. Download Apache Webserver 2.4 binary package (with OpenSSL 1.1.1) for Windows
* [Apache Webserver Docs](https://httpd.apache.org/docs/2.4/platform/windows.html)
* Binary files should be compiled for x86 using VC15
  * At the time of writing, my package was downloaded from ApacheHaus and was named like so: `httpd-2.4.41-o111c-x86-vc15-r2.zip`
  * [ApacheHaus Download List](https://www.apachehaus.com/cgi-bin/download.plx)

2. Install Apache Webserver
* Unpack the zip file into the root directory of your Windows installation 
  * Preferably with a simple folder name of `apache` or `apache24`
* Run the following command from the /bin/ directory of your Apache installation while in cmd prompt:
```
httpd.exe -k install
```
* Open ApacheMonitor.exe from the same directory whenever you need quick access to control the service

3. Configure Apache Webserver
* Open httpd.conf file in your /conf/ directory of your Apache installation
* Set the ServerRoot and ServerName variables correctly (they should be defined already so search for them)
  * ServerRoot assignment for example installation in root directory as follows:
```
Define SRVROOT "/Apache24"
ServerRoot "${SRVROOT}"
```
  * Use localhost for ServerName as follows:
```
ServerName localhost:80
```

4. Test the installation
* Open a web browser and navigate to the following URL:
```
http://localhost
```
- or -
```
http://127.0.0.1/
```
* The page should read **It works!**

## PHP 7.3 Apache module for Windows
1. Download PHP 7.3 **Thread Safe** binary package for Windows
* [PHP Downloads (.zip file)](https://windows.php.net/download/)
* Binary files should be compiled for **x86** using **VC15** and **Thread Safe** in order to function with Apache Webserver
  * At the time of writing, my package was named like so: `php-7.3.9-Win32-VC15-x86.zip`
  
2. Install PHP 7.3
* Unpack the zip file into the root directory of your Windows installation 
  *Preferably with a simple folder name of `php` or `php73`

3. Configure the PHP module for Apache Webserver
* In the PHP root directory, change the name of file `php.ini-development` to `php.ini`
  * Open `php.ini` and set the extension directory variable (`extension_dir`) explicitly:
```
extension_dir = "c:\php73\ext"
```
  * Uncomment the `;` from the MySQLi extension list in the .ini file like so:
```
extension=mysqli
```
* Add your PHP directory to the Windows path variable
  * Open Environment Variables of the System Properties menu of Windows
  * The system variable `Path` needs the PHP directory added to it like so:
```
C:\previouspathvalues;C:\PHP;
```
* Modify your Apache Webserver .conf file to link it with PHP
  * Search the file for DirectoryIndex variable and modify it like so:
```
DirectoryIndex index.php index.html
```
  * Add these variables to the end of the file:
```
PHPIniDir "c:\php73"
AddHandler application/x-httpd-php .php
LoadModule php7_module "C:\PHP73/php7apache2_4.dll"
```

4.  Test the integration of PHP and Apache
* Restart your Apache Webserver (using Apache Monitor is easiest)
  * If it will not start or restart, there is an error with the configuration variables that have been added
* Open the command prompt in any folder except PHP root and type `php -m`
  * If the command is unrecognized, then we did not set the path correctly (or Apache is not running)
  * One of the modules named in the list should be `mysqli` (this may fail if MySQL is not yet installed or if configuration is erroneous)
* Create a file named `<something>.php` and place it in your Apache `/htdocs/` folder with the following text:
```
<?php phpinfo(); ?>
```
  * Go to your web browser and navigate to `http://localhost/<something>.php`
  * The page should return data about your php installation and all of the modules installed with it
  * Search for `mysqli` to see if the module was properly configured with PHP; if it does not exist, PHP has not linked with the SQL native driver

## MySQL
* **Coming soon!**