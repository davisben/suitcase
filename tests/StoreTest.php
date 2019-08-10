<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
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
            ],
        ];
    }

    /**
     * Test that data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveWithoutCollection($array): void
    {
        $this->expectException(Exception\CollectionException::class);

        $store = new Store($this->filesystem->reveal());
        $store->save('data', $array);
    }

    /**
     * Test that new data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveNew($array): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(false);
        $this->filesystem->write($path, Json::encode($array))->willReturn(true);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $return = $store->save('data', $array);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that new data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveNewError($array): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(false);
        $this->filesystem->write($path, Json::encode($array))->willReturn(false);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that new data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveNewFileExists($array): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(false);
        $this->filesystem->write($path, Json::encode($array))->willThrow(FileExistsException::class);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that updated data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveExisting($array): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(true);
        $this->filesystem->update($path, Json::encode($array))->willReturn(true);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $return = $store->save('data', $array);
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveExistingError($array): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(true);
        $this->filesystem->update($path, Json::encode($array))->willReturn(false);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that data is saved to the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testSaveExistingFileNotFound($array): void
    {
        $this->expectException(Exception\SaveException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->has($path)->willReturn(true);
        $this->filesystem->update($path, Json::encode($array))->willThrow(FileNotFoundException::class);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $store->save('data', $array);
    }

    /**
     * Test that a file is read from the store.
     *
     * @dataProvider jsonDataProvider
     */
    public function testReadFile($array): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->read($path)->willReturn(Json::encode($array));
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $return = $store->read('data');
        $this->assertEquals($array, $return);
    }

    /**
     * Test that an exception is thrown when a file can't be read from the store.
     */
    public function testReadError(): void
    {
        $this->expectException(Exception\ReadException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->read($path)->willReturn(false);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $store->read('data');
    }

    /**
     * Test that all files are read from the collection.
     *
     * @dataProvider jsonDataProvider
     */
    public function testReadAll($array): void
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
        $this->filesystem->read($contents[0]['path'])->willReturn(Json::encode($array));
        $this->filesystem->read($contents[1]['path'])->willReturn(Json::encode($array));
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $return = $store->readAll();
        $this->assertEquals($data, $return);
    }

    /**
     * Test that a file is deleted from the store.
     */
    public function testDeleteFile(): void
    {
        $path = self::$collection . '/data.json';
        $this->filesystem->delete($path)->willReturn(true);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $return = $store->delete('data');
        $this->assertInstanceOf(Store::class, $return);
    }

    /**
     * Test that an exception is thrown when a file can't be deleted from the store.
     */
    public function testDeleteError(): void
    {
        $this->expectException(Exception\DeleteException::class);

        $path = self::$collection . '/data.json';
        $this->filesystem->delete($path)->willReturn(false);
        $store = new Store($this->filesystem->reveal());
        $store->setCollection(self::$collection);

        $store->delete('data');
    }
}
