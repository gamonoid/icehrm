Dot Access Data
===============

Given a deep data structure, access data by dot notation.


Requirements
------------

 * PHP (5.3+)


Usage
-----

Abstract example:

```php
use Dflydev\DotAccessData\Data;

$data = new Data;

$data->set('a.b.c', 'C');
$data->set('a.b.d', 'D1');
$data->append('a.b.d', 'D2');
$data->set('a.b.e', array('E0', 'E1', 'E2'));

// C
$data->get('a.b.c');

// array('D1', 'D2')
$data->get('a.b.d');

// array('E0', 'E1', 'E2')
$data->get('a.b.e');

// true
$data->has('a.b.c');

// false
$data->has('a.b.d.j');
```

A more concrete example:

```php
use Dflydev\DotAccessData\Data;

$data = new Data(array(
    'hosts' => array(
        'hewey' => array(
            'username' => 'hman',
            'password' => 'HPASS',
            'roles' => array('web'),
        ),
        'dewey' => array(
            'username' => 'dman',
            'password' => 'D---S',
            'roles' => array('web', 'db'),
            'nick' => 'dewey dman'
        ),
        'lewey' => array(
            'username' => 'lman',
            'password' => 'LP@$$',
            'roles' => array('db'),
        ),
    )
));

// hman
$username = $data->get('hosts.hewey.username');
// HPASS
$password = $data->get('hosts.hewey.password');
// array('web')
$roles = $data->get('hosts.hewey.roles');
// dewey dman
$nick = $data->get('hosts.dewey.nick');
// Unknown
$nick = $data->get('hosts.lewey.nick', 'Unknown');

// DataInterface instance
$dewey = $data->getData('hosts.dewey');
// dman
$username = $dewey->get('username');
// D---S
$password = $dewey->get('password');
// array('web', 'db')
$roles = $dewey->get('roles');

// No more lewey
$data->remove('hosts.lewey');

// Add DB to hewey's roles
$data->append('hosts.hewey.roles', 'db');

$data->set('hosts.april', array(
    'username' => 'aman',
    'password' => '@---S',
    'roles' => array('web'),
));

// Check if a key exists (true to this case)
$hasKey = $data->has('hosts.dewey.username');
```


License
-------

This library is licensed under the New BSD License - see the LICENSE file
for details.


Community
---------

If you have questions or want to help out, join us in the
[#dflydev](irc://irc.freenode.net/#dflydev) channel on irc.freenode.net.
