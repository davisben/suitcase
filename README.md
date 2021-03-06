# Suitcase

[![Build Status](https://travis-ci.org/davisben/suitcase.svg?branch=master)](https://travis-ci.org/davisben/suitcase) [![Coverage](https://sonarcloud.io/api/project_badges/measure?project=davisben_suitcase&metric=coverage)](https://sonarcloud.io/dashboard?id=davisben_suitcase) [![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=davisben_suitcase&metric=security_rating)](https://sonarcloud.io/dashboard?id=davisben_suitcase)

Suitcase is a flat file data store that takes advantage of other libraries to abstact the filesystem and encoding/decoding of data. The filesystem storage is handled by [Flysystem](https://flysystem.thephpleague.com/), so data can be stored locally, in memory, on S3, etc. The data processing is handled by [Ivory Serializer](https://github.com/egeloen/ivory-serializer), which supports JSON, YAML, XML, and CSV formats. Data is stored in collections, which are subdirectories of the primary store directory. Items can be accessed individually, or an entire collection can be returned.

## Requirements
- PHP 7.1+
- PHP JSON extension

## Installation
```
composer require davisben/suitcase
```

## Usage
Suitcase requires a Flysystem Filesystem object to act as an interface to the filesystem. The following example uses the Local adapter. For more information, see https://flysystem.thephpleague.com/docs/usage/setup

Also required is a Serializer object to use to encode and decode the data.

#### Create the store
```php
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Ivory\Serializer\Serializer;
use Suitcase\Format\Json;
use Suitcase\Store;

$adapter = new Local(__DIR__ . '/data');
$filesystem = new Filesystem($adapter);
$serializer = new Serializer();
$formatter = new Json($serializer);
$store = new Store($filesystem, $formatter);
$store->setCollection('collection');
```

#### Working with data
```php
// Saves data into file.json.
$data = ['some' => 'data'];
$store->save('file', $data); 

// Reads data from file.json.
$data = $store->read('file');

// Reads all data from the current collection.
$data = $store->readAll();

// Deletes file.json.
$store->delete('file'); 

// Deletes all files in the current collection.
$store->deleteAll();

// Delete a collection, and all files within.
$store->deleteCollection('collection');
```

## License
Suitcase is licensed under the [MIT License](https://github.com/davisben/suitcase/blob/master/LICENSE).
