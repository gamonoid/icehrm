****
Mail
****
Class that provides multiple interfaces for sending emails.

PEAR's Mail package defines an interface for implementing mailers under the
PEAR hierarchy.
It also provides supporting functions useful to multiple mailer backends.

Currently supported backends include:

- PHP's native ``mail()`` function
- sendmail
- SMTP

This package also provides a `RFC 822`__ email address list validation utility class.

Use Mail in combination with `Mail_Mime`__ to send HTML emails or emails with
attachments - have a look at the example__.

__ https://tools.ietf.org/html/rfc822
__ http://pear.php.net/package/Mail_Mime
__ http://pear.php.net/manual/en/package.mail.mail-mime.example.php

============
Installation
============

PEAR
====
::

    $ pear install mail

Composer
========
::

    $ composer require pear/mail

=====
Links
=====
Homepage
  http://pear.php.net/package/Mail
Source code
  https://github.com/pear/Mail
Issue tracker
  http://pear.php.net/bugs/search.php?cmd=display&package_name[]=Mail
Unit test status
  https://travis-ci.org/pear/Mail
Packagist
  https://packagist.org/packages/pear/mail
