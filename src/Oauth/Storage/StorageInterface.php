<?php


namespace Cidaas\OauthConnect\Oauth\Storage;

/**
 * Oauth storage manager interface
 */
interface StorageInterface
{
    /**
    * Retrieve a item from storage
    *
    * @param string $key
    *
    * @return mixed
    */
    public function get($key);

    /**
    * Add or Update an item to storage
    *
    * @param string $key
    * @param string $value
    */
    public function set($key, $value);

    /**
    * Delete an item from storage
    *
    * @param string $key
    */
    public function delete($key);

    /**
    * Delete a item from storage
    *
    * @param string $key
    */
    public function deleteMatch($key);

    /**
    * Clear all items in storage
    */
    public function clear();
}
