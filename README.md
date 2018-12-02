# An account-password backend system

The end product consists of 3 endpoints: signUp, login and verify email.
Is based on php7.1, symfony 4.2 and mysql. 




## Instalation 
composer.phar install 


## Configuration 
Change file .env  or create .env.local file with database name, user and password:
``` 
    DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name 
```
For example, for user root without password and database with name db_auth we configure:
```
    DATABASE_URL=mysql://root:@127.0.0.1:3306/db_auth
``` 
For create database and the tables we execute:
```
bin/console doctrine:database:create
bin/console doctrine:schema:create
``` 

In .env or .env.local you can configure Swiftmailer for sendig mails with activation link. 
```
# For Gmail as a transport, use: "gmail://username:password@localhost"
# For a generic SMTP server, use: "smtp://localhost:25?encryption=&auth_mode="
# Delivery is disabled by default via "null://localhost"
MAILER_URL=null://localhost
```  
For the dev environment the emails are not send, are saved in /var/log/spooldir.


## Run
In dev environment we run: 
```
    bin/console server:run
```


## Tests
We use ant to automatically run some operation like:
 * php lint(php -l) 
 * php check style (https://github.com/squizlabs/PHP_CodeSniffer), 
 * tests (https://phpunit.de/getting-started/phpunit-7.html)
 * clear cache
 * initialize database
  
All this configuration are in the file build.xml. 
Configure the path where php, phpcs, phpunit are located.

