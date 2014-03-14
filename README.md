OpenCart Cache
=======================

Enables APC caching with degradation to a standard file cache if APC is not loaded.

How to install
--------------
Copy the cache.php to a ```system/library/``` folder with replacement.

You can set the preferred cache provider in ```index.php``` and ```admin/index.php``` like this:
```php
// Cache
$cache = new Cache('apc'); // supported 'apc', 'memcache' and 'file'
$registry->set('cache', $cache);
```

If you want to use the Memcache you have to add Memcache server host and port configuration  in the config files.
```php
// Cache settings
define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', '11211');
```

Enhancements
--------------
Added methods

* ```has($key)``` verify the existence of the cache by key
* ```flush()``` delete all entries from the cache
* ```setExpire()``` update ttl value in seconds
* ```getExpire()``` return current ttl in seconds