<?php

namespace Suitcase;

use League\Flysystem\FilesystemInterface;
use Suitcase\Format\Json;

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
    public function collection($collection): Store
    {
        $this->collection = $collection;
        return $this;
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
     * @throws \Exception
     *   Throws an exception if saving fails.
     */
    public function save($key, $data): Store
    {
        if (empty($this->collection)) {
            throw new \Exception('Collection not set.');
        }

        $formatter = $this->options['format'];
        $path = $this->getFilePath($key);
        $encoded_data = $formatter::encode($data);

        $exists = $this->filesystem->has($path);

        if ($exists) {
            $response = $this->filesystem->update($path, $encoded_data);
        } else {
            $response = $this->filesystem->write($path, $encoded_data);
        }

        if (!$response) {
            throw new \Exception('Unable to save data.');
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
            throw new \Exception('Unable to read data.');
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
            throw new \Exception('Unable to delete data.');
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
     */
    protected function getFilePath($key): string
    {
        $formatter = $this->options['format'];
        return $this->collection . '/' . $key . $formatter::FILE_EXT;
    }
}
