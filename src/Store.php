<?php

namespace Suitcase;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FileExistsException;
use Suitcase\Format\Json;
use Suitcase\Exception;

class Store
{
    /**
     * Configuration options.
     *
     * @var array
     */
    private $options;

    /**
     * The Filesystem.
     *
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * The collection to use for the store.
     *
     * @var string
     */
    private $collection;

    /**
     * Constructs a Store object.
     *
     * @param \League\Flysystem\FilesystemInterface $filesystem
     *   A Filesystem object to use for the store.
     * @param array $options (optional)
     *   An array of configuration options.
     */
    public function __construct(FilesystemInterface $filesystem, $options = [])
    {
        $this->filesystem = $filesystem;

        $defaults = [
          'format' => Json::class,
        ];
        $this->options = array_merge($defaults, $options);
    }

    /**
     * Sets the collection to use.
     *
     * @param string $collection
     *   The name of the collection.
     *
     * @return \Suitcase\Store
     *   The store object.
     */
    public function setCollection($collection): Store
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Writes new data to the store.
     *
     * @param string $path
     *   The path to the file being saved.
     * @param string $data
     *   The encoded data to be stored.
     *
     * @throws \Suitcase\Exception\SaveException
     *   Throws an exception if writing fails.
     */
    protected function write($path, $data): void
    {
        try {
            $response = $this->filesystem->write($path, $data);
        } catch (FileExistsException $e) {
            throw new Exception\SaveException('Unable to write data. File already exists.', $e->getMessage());
        }

        if (!$response) {
            $error = error_get_last();
            throw new Exception\SaveException('Unable to write data.', $error['message']);
        }
    }

    /**
     * Updates existing data in the store.
     *
     * @param string $path
     *   The path to the file being saved.
     * @param string $data
     *   The encoded data to be stored.
     *
     * @throws \Suitcase\Exception\SaveException
     *   Throws an exception if updating fails.
     */
    protected function update($path, $data): void
    {
        try {
            $response = $this->filesystem->update($path, $data);
        } catch (FileNotFoundException $e) {
            throw new Exception\SaveException('Unable to update data. File not found.', $e->getMessage());
        }

        if (!$response) {
            $error = error_get_last();
            throw new Exception\SaveException('Unable to update data.', $error['message']);
        }
    }

    /**
     * Saves data to the store.
     *
     * @param string $key
     *   The key to use for the item being saved.
     * @param array $data
     *   An array of data to save.
     *
     * @return \Suitcase\Store
     *   The store object.
     *
     * @throws \Suitcase\Exception\CollectionException
     *   Throws an exception if a collection is not set.
     * @throws \Suitcase\Exception\SaveException
     *   Throws an exception if saving fails.
     */
    public function save($key, $data): Store
    {
        $formatter = $this->options['format'];
        $path = $this->getFilePath($key);
        $encoded_data = $formatter::encode($data);

        $exists = $this->filesystem->has($path);

        if ($exists) {
            $this->update($path, $encoded_data);
        } else {
            $this->write($path, $encoded_data);
        }

        return $this;
    }

    /**
     * Gets data saved in the store.
     *
     * @param string $key
     *   The key of the item being read.
     *
     * @return array
     *   The saved data.
     *
     * @throws \Exception
     *   Throws an exception if reading fails.
     */
    public function read($key): array
    {
        $formatter = $this->options['format'];
        $path = $this->getFilePath($key);
        $encoded_data = $this->filesystem->read($path);

        if (!$encoded_data) {
            throw new Exception\ReadException('Unable to read data.');
        }

        return $formatter::decode($encoded_data);
    }

    /**
     * Deletes an item saved in the store.
     *
     * @param string $key
     *   The key of the item being deleted.
     *
     * @return \Suitcase\Store
     *   The store object.
     *
     * @throws \Exception
     *   Throws an exception if deleting fails.
     */
    public function delete($key): Store
    {
        $path = $this->getFilePath($key);
        $response = $this->filesystem->delete($path);

        if (!$response) {
            throw new Exception\DeleteException('Unable to delete data.');
        }

        return $this;
    }

    /**
     * Generates the path to a file.
     *
     * @param string $key
     *   The key of the store item.
     *
     * @return string
     *   The path to the file.
     *
     * @throws \Suitcase\Exception\CollectionException
     *   Throws an exception if a collection is not set.
     */
    protected function getFilePath($key): string
    {
        if (!$this->collection) {
            throw new Exception\CollectionException('Collection not set.');
        }

        $formatter = $this->options['format'];
        return $this->collection . '/' . $key . $formatter::FILE_EXT;
    }
}
