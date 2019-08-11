<?php

namespace Suitcase;

use Ivory\Serializer\SerializerInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FileExistsException;
use Suitcase\Format\Json;
use Suitcase\Exception;

class Store
{
    /**
     * The Filesystem.
     *
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * The formatter to use for encoding and decoding data.
     *
     * @var \Suitcase\Format\FormatInterface
     */
    private $formatter;

    /**
     * Configuration options.
     *
     * @var array
     */
    private $options;

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
     * @param \Ivory\Serializer\SerializerInterface $serializer
     *   The serializer used to encode and decode data.
     * @param array $options (optional)
     *   An array of configuration options.
     */
    public function __construct(FilesystemInterface $filesystem, SerializerInterface $serializer, $options = [])
    {
        $this->filesystem = $filesystem;

        $defaults = [
          'format' => Json::class,
        ];
        $this->options = array_merge($defaults, $options);

        $format = $this->options['format'];
        $this->formatter = new $format($serializer);
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
     * Delete a collection.
     *
     * @param string $collection
     *   The name of the collection.
     * @param bool $empty (optional)
     *   Boolean indicating whether or no to delete all files in the
     *   collection. If false, an exception will be thrown if the
     *   collection is not empty. Defaults to true.
     *
     * @return \Suitcase\Store
     *   The store object.
     *
     * @throws \Suitcase\Exception\CollectionNotEmptyException
     *   Throws an exception if the collection is not empty.
     * @throws \Suitcase\Exception\DeleteException
     *   Throws an exception if deleting fails.
     */
    public function deleteCollection($collection, $empty = true): Store
    {
        if ($empty) {
            $response = $this->filesystem->deleteDir($collection);
        } else {
            $files = $this->filesystem->listContents($collection);
            if (!empty($files)) {
                throw new Exception\CollectionNotEmptyException('Collection is not empty.');
            }
        }

        if (!$response) {
            $error = error_get_last();
            throw new Exception\DeleteException('Unable to delete collection.', $error['message']);
        }

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
        $path = $this->getFilePath($key);
        $encoded_data = $this->formatter->encode($data);

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
     * @throws \Suitcase\Exception\CollectionException
     *   Throws an exception if a collection is not set.
     * @throws \Suitcase\Exception\ReadException
     *   Throws an exception if reading fails.
     */
    public function read($key): array
    {
        $path = $this->getFilePath($key);

        try {
            $encoded_data = $this->filesystem->read($path);
        } catch (FileNotFoundException $e) {
            throw new Exception\ReadException('Unable to read data. File not found.', $e->getMessage());
        }

        if (!$encoded_data) {
            throw new Exception\ReadException('Unable to read data.');
        }

        return $this->formatter->decode($encoded_data);
    }

    /**
     * Reads all data saved in the current collection.
     *
     * @return array
     *   An array of saved data.
     *
     * @throws \Suitcase\Exception\CollectionException
     *   Throws an exception if a collection is not set.
     * @throws \Suitcase\Exception\ReadException
     *   Throws an exception if reading fails.
     */
    public function readAll(): array
    {
        $data = [];
        $files = $this->filesystem->listContents($this->collection);

        foreach ($files as $file) {
            $data[$file['filename']] = $this->read($file['filename']);
        }

        return $data;
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
     * @throws \Suitcase\Exception\CollectionException
     *   Throws an exception if a collection is not set.
     * @throws \Suitcase\Exception\DeleteException
     *   Throws an exception if deleting fails.
     */
    public function delete($key): Store
    {
        $path = $this->getFilePath($key);

        try {
            $response = $this->filesystem->delete($path);
        } catch (FileNotFoundException $e) {
            throw new Exception\DeleteException('Unable to delete data. File not found.', $e->getMessage());
        }

        if (!$response) {
            throw new Exception\DeleteException('Unable to delete data.');
        }

        return $this;
    }

    /**
     * Deletes all data saved in the current collection.
     *
     * @return \Suitcase\Store
     *   The store object.
     *
     * @throws \Suitcase\Exception\CollectionException
     *   Throws an exception if a collection is not set.
     * @throws \Suitcase\Exception\DeleteException
     *   Throws an exception if deleting fails.
     */
    public function deleteAll(): Store
    {
        $files = $this->filesystem->listContents($this->collection);

        foreach ($files as $file) {
            $this->delete($file['filename']);
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

        return $this->collection . '/' . $key . $this->formatter->getExtension();
    }
}
