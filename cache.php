<?php

/**
 * Class Cache
 */
class Cache implements \CacheInterface
{
    /**
     * @var APCCache|FileCache|MemcacheCache
     */
    private $provider;

    /**
     * @param string $provider
     * @param string $prefix
     */
    public function __construct($provider = null, $prefix = DB_DATABASE)
    {
        $factory = new \CacheFactory();
        $this->provider = $factory->createProvider($provider);
        $this->setPrefix($prefix . '.');
    }

    /**
     * @inheritdoc
     */
    public function getExpire()
    {
        return $this->provider->getExpire();
    }

    /**
     * @inheritdoc
     */
    public function setExpire($ttl)
    {
        $this->provider->setExpire($ttl);
    }

    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->provider->getPrefix();
    }

    /**
     * @inheritdoc
     */
    public function setPrefix($prefix)
    {
        $this->provider->setPrefix($prefix);
    }

    /**
     * @return APCCache|FileCache|MemcacheCache
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->provider->get($key);
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return $this->provider->has($key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return $this->provider->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        return $this->provider->delete($key);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        return $this->provider->flush();
    }
}

class CacheFactory
{
    /**
     * @param $provider
     * @return APCCache|FileCache|MemcacheCache
     */
    public function createProvider($provider)
    {
        switch ($provider) {
            case 'apc':
                if (extension_loaded('apc')) {
                    return new \APCCache();
                }
            case 'memcache':
                if (extension_loaded('memcache')) {
                    return new \MemcacheCache();
                }
            case 'file':
            default:
                return new \FileCache();
        }
    }
}

class APCCache implements \CacheInterface
{
    /**
     * @var int
     */
    private $expire;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @inheritdoc
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @inheritdoc
     */
    public function setExpire($ttl)
    {
        $this->expire = $ttl;
    }

    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return apc_fetch($this->getPrefix() . $key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return apc_store($this->getPrefix() . $key, $value, $this->getExpire());
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return apc_exists($this->getPrefix() . $key);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        return apc_delete($this->getPrefix() . $key);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        return apc_clear_cache();
    }
}

class MemcacheCache implements \CacheInterface
{
    /**
     * @var int
     */
    private $expire;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var \Memcache
     */
    private $memcache;

    function __construct()
    {
        $this->memcache = new Memcache();
        $this->memcache->connect(MEMCACHE_HOST, MEMCACHE_PORT);
    }

    /**
     * @inheritdoc
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @inheritdoc
     */
    public function setExpire($ttl)
    {
        $this->expire = $ttl;
    }

    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @inheritdoc
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->memcache->get($this->getPrefix() . $key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return $this->memcache->add($this->getPrefix() . $key, $value, 0, $this->getExpire());
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        $result = false;
        $data = $this->memcache->get($this->getPrefix() . $key);
        if ($data) {
            $result = true;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        return $this->memcache->delete($this->getPrefix() . $key);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        return $this->memcache->flush();
    }
}

class FileCache implements \CacheInterface
{
    /**
     * @var int
     */
    private $expire;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @inheritdoc
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @inheritdoc
     */
    public function setExpire($ttl)
    {
        $this->expire = $ttl;
    }

    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @inheritdoc
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $data = false;
        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $this->getPrefix() . $key) . '.*');
        if ($files) {
            $cache = file_get_contents($files[0]);
            $data = unserialize($cache);

            foreach ($files as $file) {
                $time = substr(strrchr($file, '.'), 1);
                if ($time < time()) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->delete($key);

        $file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $this->getPrefix() . $key) . '.' . (time() + $this->getExpire());
        $handle = fopen($file, 'w');
        fwrite($handle, serialize($value));
        return fclose($handle);
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        $result = false;
        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $this->getPrefix() . $key) . '.*');
        if ($files) {
            $cache = file_get_contents($files[0]);
            if ($cache) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $result = false;
        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $this->getPrefix() . $key) . '.*');
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $result = true;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $files = glob(DIR_CACHE . 'cache.' . $this->getPrefix() . '*');
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        return true;
    }
}

interface CacheInterface
{
    /**
     * @param $key string
     * @return string|bool
     */
    public function get($key);

    /**
     * @param $key string
     * @param $value string|array
     * @return bool
     */
    public function set($key, $value);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param $key
     * @return bool
     */
    public function delete($key);

    /**
     * Flush all existing items at the server
     * @return bool
     */
    public function flush();

    /**
     * @return mixed
     */
    public function getExpire();

    /**
     * @param $ttl int
     * @return void
     */
    public function setExpire($ttl);

    /**
     * @return int
     */
    public function getPrefix();

    /**
     * @param $prefix string
     * @return void
     */
    public function setPrefix($prefix);
}
