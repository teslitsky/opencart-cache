<?php

class Cache
{
    private $expire = 3600;

    public function get($key)
    {
        if (extension_loaded('apc')) {
            return apc_fetch($this->getPrefix() . $key);
        }

        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');
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

            return $data;
        }
    }

    public function has($key)
    {
        if (extension_loaded('apc')) {
            return apc_exists($this->getPrefix() . $key);
        }

        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');
        if ($files) {
            $cache = file_get_contents($files[0]);
            if ($cache) {
                return true;
            }
        }

        return false;
    }

    public function set($key, $value)
    {
        if (extension_loaded('apc')) {
            return apc_store($this->getPrefix() . $key, $value, $this->expire);
        }

        $this->delete($key);

        $file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $this->expire);
        $handle = fopen($file, 'w');
        fwrite($handle, serialize($value));
        fclose($handle);
    }

    public function delete($key)
    {
        if (extension_loaded('apc')) {
            return apc_delete($this->getPrefix() . $key);
        }

        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function flush()
    {
        if (extension_loaded('apc')) {
            return apc_clear_cache();
        }

        $files = glob(DIR_CACHE . 'cache.*');
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function setExpire($ttl)
    {
        $this->expire = $ttl;
    }

    public function getExpire()
    {
        return $this->expire;
    }

    private function getPrefix()
    {
        return HTTP_SERVER;
    }
}
