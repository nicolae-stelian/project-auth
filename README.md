# An account-password backend system

The end product consists of 3 endpoints: signUp, login and verify email.
Is based on php7.1, symfony 4.2 and mysql. 


## Instalation 
composer.phar install 

## Run 
bin/console server:run


## Tests
We use ant to automatically run some operation like:
 * php lint(php -l) 
 * php check style (https://github.com/squizlabs/PHP_CodeSniffer), 
 * tests (https://phpunit.de/getting-started/phpunit-7.html)
 * clear cache
 * initialize database
  
All this configuration are in the file build.xml. 
Configure the path where php, phpcs, phpunit are located.

