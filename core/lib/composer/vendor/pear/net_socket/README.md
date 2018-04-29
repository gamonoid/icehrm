# Net_Socket - Network Socket Interface

[![Build Status](https://travis-ci.org/pear/Net_Socket.svg?branch=master)](https://travis-ci.org/pear/Net_Socket)
    

Net_Socket is a class interface to TCP sockets. It provides blocking
and non-blocking operation, with different reading and writing modes
(byte-wise, block-wise, line-wise and special formats like network
byte-order ip addresses).

[Homepage](http://pear.php.net/package/Net_Socket/)


## Installation
For a PEAR installation that downloads from the PEAR channel:

`$ pear install pear/net_socket`

For a PEAR installation from a previously downloaded tarball:

`$ pear install Net_Socket-*.tgz`

For a PEAR installation from a code clone:

`$ pear install package.xml`

For a local composer installation:

`$ composer install`

To add as a dependency to your composer-managed application:

`$composer require pear/net_socket`


## Tests
Run  the tests from a local composer installation:

`$ ./vendor/bin/phpunit`


## License
BSD-2 license
