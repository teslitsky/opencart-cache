OpenCart Cache
=======================

Enables APC caching with degradation to a standard file cache if APC is not loaded.

How to install
--------------
Copy the cache.php to a ```system/library/``` folder with replacement.

Enhancements
--------------
Added methods

* ```has($key)``` verify the existence of the cache by key
* ```flush()``` delete all entries from the cache
* ```setExpire()``` update ttl value in seconds
* ```getExpire()``` return current ttl in seconds