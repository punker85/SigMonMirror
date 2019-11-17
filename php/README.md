# Access.xml Setup for PHP-MySQL communication

## Access.xml stores sensitive database information
1. Place the file in directory above the HTML public web root folder
* Web root folders like `/var/www/` `/srv/www` `/srv/html` `/htdocs` or `/public_html`
* Chmod the file appropriately so the web server user has access to read it

2. Edit the node values to match your specific MySQL database implementation
* `<host>` is the `localhost`, domain name, or IP address of the database server
* `<user>` is the username for accessing the database server
* `<password>` is the login password for that database server with username
* `<schema>` is the name of the database that you will use
* `<passkey>` is a simple security value for database insertion from PHP scripts
  * Matching this value whenever JSON API is called is key to preventing random inserts from foreign/unknown sources
