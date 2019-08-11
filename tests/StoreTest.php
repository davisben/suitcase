<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Ivory\Serializer\SerializerInterface;
use Ivory\Serializer\Format;
use Suitcase\Format\Json;
use Suitcase\Exception;

class StoreTest extends TestCase
{
    /**
     * The collection name.
     *
     * @var string
     */
    protected static $collection;

    /**
     * A prophesized mock filesystem object.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $filesystem;

    /**
     * A prophesized mock serializer object.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $serializer;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$collection = 'collection';
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->filesystem = self::prophesize(FilesystemInterface::class);
        $this->serializer = self::prophesize(SerializerInterface::class);
    }

    /**
     * Provides JSON data for store tests.
     *
     * @return array
     *   An array of data.
     */
    public function jsonDataProvider(): array
    {
        return [
            [
                [
                    'foo' => [
                        'bar' => 'baz',
                    ],
                ],
                '{"foo":{"bar":"baz"}}',
            ],
        ];
    }

    /**
     * Test that an exception is thrown when a collection has not been set.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveWithoutCollection($array): void
    {
        $this->expectException(Exception\CollectionException::class);

        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->save('data', $array);
    }

    /**
     * Test deleting a collection.
     */
    public function testDeleteCollection(): void
    {
        $this->filesystem->deleteDir(self::$collection)->willReturn(true);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);

        $return = $store->deleteCollection(self::$collection);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test deleting a collection.
     */
    public function testDeleteCollectionError(): void
    {
        $this->expectException(Exception\DeleteException::class);

        $this->filesystem->deleteDir(self::$collection)->willReturn(false);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);

        $return = $store->deleteCollection(self::$collection);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test deleting a collection.
     */
    public function testDeleteCollectionNotEmpty(): void
    {
        $this->expectException(Exception\CollectionNotEmptyException::class);

        $contents = [
          [
            'path' => self::$collection . '/data.json',
            'filename' => 'data',
          ],
          [
            'path' => self::$collection . '/another.json',
            'filename' => 'another',
          ],
        ];
        $this->filesystem->listContents(self::$collection)->willReturn($contents);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);

        $return = $store->deleteCollection(self::$collection, false);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that new data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveNew($array, $json): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(false);
        $this->filesystem->write($path, $json)->willReturn(true);
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $return = $store->save('data', $array);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that an exception is thrown when there is an error writing a file.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveNewError($array, $json): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(false);
        $this->filesystem->write($path, $json)->willReturn(false);
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that an exception is thrown when the file to write already exists.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveNewFileExists($array, $json): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(false);
        $this->filesystem->write($path, $json)->willThrow(FileExistsException::class);
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that updated data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveExisting($array, $json): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(true);
        $this->filesystem->update($path, $json)->willReturn(true);
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $return = $store->save('data', $array);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that an exception is thrown when there is an error updating a file.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveExistingError($array, $json): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(true);
        $this->filesystem->update($path, $json)->willReturn(false);
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that an exception is thrown when the file to update can't be found.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveExistingFileNotFound($array, $json): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(true);
        $this->filesystem->update($path, $json)->willThrow(FileNotFoundException::class);
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that a file is read from the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testReadFile($array, $json): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->read($path)->willReturn($json);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $this->serializer->deserialize($json, 'array', Format::JSON)->willReturn($array);
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $return = $store->read('data');
        $this->assertEquals($array, $return);
    }

    /**
     * Test that an exception is thrown when a file can't be read.
     */
    public function testReadError(): void
    {
        $this->expectException(Exception\ReadException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->read($path)->willReturn(false);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->read('data');
    }

    /**
     * Test that an exception is thrown when the file to read can't be found.
     */
    public function testReadFileNotFound(): void
    {
        $this->expectException(Exception\ReadException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->read($path)->willThrow(FileNotFoundException::class);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->read('data');
    }

    /**
     * Test that all files are read from the collection.
     *
     * @dataProvider jsonDataProvider
     */
    public function testReadAll($array, $json): void
    {
        $data = [
            'data' => $array,
            'another' => $array,
        ];
        $contents = [
            [
                'path' => self::$collection . '/data.json',
                'filename' => 'data',
            ],
            [
                'path' => self::$collection . '/another.json',
                'filename' => 'another',
            ],
        ];
        $this->filesystem->listContents(self::$collection)->willReturn($contents);
        $this->filesystem->read($contents[0]['path'])->willReturn($json);
        $this->filesystem->read($contents[1]['path'])->willReturn($json);
        $this->serializer->deserialize($json, 'array', Format::JSON)->willReturn($array);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $return = $store->readAll();
        $this->assertEquals($data, $return);
    }

    /**
     * Test that a file is deleted.
     */
    public function testDeleteFile(): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->delete($path)->willReturn(true);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $return = $store->delete('data');
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that an exception is thrown when a file can't be deleted.
     */
    public function testDeleteError(): void
    {
        $this->expectException(Exception\DeleteException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->delete($path)->willReturn(false);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->delete('data');
    }

    /**
     * Test that an exception is thrown when the file to delete can't be found.
     */
    public function testDeleteFileNotFound(): void
    {
        $this->expectException(Exception\DeleteException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->delete($path)->willThrow(FileNotFoundException::class);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $store->delete('data');
    }

    /**
     * Test that all files are deleted from the collection.
     *
     * @dataProvider jsonDataProvider
     */
    public function testDeleteAll($array): void
    {
        $contents = [
          [
            'path' => self::$collection . '/data.json',
            'filename' => 'data',
          ],
          [
            'path' => self::$collection . '/another.json',
            'filename' => 'another',
          ],
        ];
        $this->filesystem->listContents(self::$collection)->willReturn($contents);
        $this->filesystem->delete($contents[0]['path'])->willReturn(true);
        $this->filesystem->delete($contents[1]['path'])->willReturn(true);
        $filesystem = $this->filesystem->reveal();
        $serializer = $this->serializer->reveal();
        $store = new Store($filesystem, $serializer);
        $store->setCollection(self::$collection);

        $return = $store->deleteAll();
        $this->assertInstanceOf(Store::class, $return);
    }
}
