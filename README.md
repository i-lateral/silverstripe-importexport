# SilverStripe Import/Export Module

[![Build Status](https://travis-ci.org/burnbright/silverstripe-importexport.svg?branch=master)](https://travis-ci.org/burnbright/silverstripe-importexport)

Import and export data from SilverStripe in various forms, including CSV. This module serves as a replacement/overhaul of BulkLoader functionality found in framework.

## The loading process

1. Raw data is retrieved from a source (`BulkLoaerSource`).
2. Data is provided as iterable rows (each row is heading->value mapped array).
3. Rows are mapped to a standardised format, based on a user/developer provided mapping.
4. Data is set/linked/tranformed onto a placeholder DataObject.
5. Existing record replaces placeholder, or placeholder becomes the brand new DataObject.
5. DataObject is validated and saved.
7. All results are stored in `BulkLoader_Result`.

## User-defined column mapping

Users can choose which columns map to DataObject fields. This removes any need to define headings, or headings according to a given schema.

Users can state if the first line of data is infact a heading row.

## Grid Field Importer

This is a grid field component for users to selecting a CSV file and map it's columns to data fields.

```php
$importer = new GridFieldImporter('before');
$gridConfig->addComponent($importer);
```

The importer makes use of the `CSVFieldMapper`, which displays the beginning content of a CSV.

## BulkLoaderSource

A `BulkLoaderSource` provides an iterator to get record data from. Data could come from anywhere such as a CSV file, a web API, etc.

It can be used independently from the BulkLoader to obtain data.

```php
$source = new CsvBulkLoaderSource();
$source->setFilePath("files/myfile.csv")
    ->setHasHeader(true)
    ->setFieldDelimiter(",")
    ->setFieldEnclosure("'");

foreach($source->getIterator() as $record){
    //do stuff
}
```

## (Better)BulkLoader

* Saves data from a particular source and persists it to database via the ORM.
* Determines which fields can be mapped to, either scaffoleded from the model, provided by configuration, or both.
* Detects existing records, and either skips or updates them, based on criteria.
* Maps the source data to new/existing dataobjects, based on a given mapping.
* Finds, creates, and connects relation objects to objects.
* Can clear all records prior to processing.

```php
$source = new CsvBulkLoaderSource();
$source->setFilePath("files/myfile.csv");

$loader = new BetterBulkLoader("Product");
$loader->setSource($source);

$result = $loader->load();
```

## ListBulkLoader

Often you'll want to confine bulk loading to a specific DataList. The ListBulkLoader is a varaition of BulkLoader that adds and removes records from a given DataList. Of course DataList iself doesn't have an add method implemented, so you'll probably find it more useful for a `HasManyList`.

```php
$category = ProductCategory::get()->first();

$source = new CsvBulkLoaderSource();
$source->setFilePath("productlist.csv");

$loader = new ListBulkLoader($category->Products());
$loader->setSource($source);

$result = $loader->load();
```

## Mapping record data to a standard format

You can provide a `columnMap` to map incoming records to a standard format.

```php 
$loader->columnMap = array(
    'first name' => 'FirstName',
    'Name' => 'FirstName',
    'bio' => 'Biography',
    'bday' => 'Birthday',
    'teamtitle' => 'Team.Title',
    'teamsize' => 'Team.TeamSize',
    'salary' => 'Contract.Amount'
);
```

This column map is generated by the `CSVFieldMapper` control inside the `GridFieldImporter` component.

## Transform incoming record data

You may want to perform some transformations to incoming record data. This can be done by specifying a callback against the record field name.

```php
$loader->transforms = array(
    'Code' => array(
        'callback' => function($field, $record) {
            //capitalize course codes
            return strtoupper($record[$field]));
        }
    )
);
```

## Creating and linking related DataObjects

The bulk loader can handle linking and creating `has_one` relationship objects, by either providing a callback, or using the `Relation.FieldName` style "dot notation". Relationship handling is also performed in the `transformations` array.

You can specify at the BulkLoader level if records will be created and linked, then you can also specify the behaviour for each field. The default behaviour is to both link and create relation objects.

Here are some configuration examples:

```php
$loader->transforms = array(
    //link and create courses
    'Course.Title' = array(
        'link' => true,
        'create' => true
    ),
    //only link to existing tutors
    'Tutor.Name' => array(
        'link' => true,
        'create' => false
    ),
    //custom way to find parent courses
    'Parent' => array(
        'callback' => function($value, $placeholder) use ($self){
            return Course::get()
                ->filter("Title", $value)
                ->first();
        }
    )
);
```

Note that `$placeholder` in the above example refers to a dummy DataObject that is populated in order to then be saved, or checked against for duplicates. You should not call `$placeholder->write()` in your callback.

## Determining when to overwrite existing (duplicate) DataObjects

Duplicate checks are performed on record data, mapped into the standardised form.

You can perform duplicate checking on data fields:

```php
//course is a duplicate when title is the same
$loader->duplicateChecks = array(
    "Title"
);
```

Or on a relation:
```php
//course selection is a duplicate when course is the same
$loader->duplicateChecks = array(
    "Course.Title"
);
```

Duplicates can also be found using a callback function:
```php
$loader->duplicateChecks = array(
    "FooBar" => function($fieldName, $record) {
        if(!isset($record["FirstName"]) || !isset($record["LastName"])){
            return null;
        }

        return Person::get()
            ->filter("FirstName", $record['FirstName'])
            ->filter("LastName", $record['LastName'])
            ->first();
    }
);
```

## Replace all the "legacy" ModelAdmin importers

Some simple yaml config options to help with swapping out all the importer functionality.

```yaml
ModelAdmin:
    removelegacyimporters: true
    addbetterimporters: true
```

Remove only the scafolded (non-custom) importers:
```yaml
ModelAdmin:
    removelegacyimporters: scaffolded
```

## Troubleshooting

If you are writing relation objects during loading, and they fail validation, the loader will simply ignore that relation object.

## Contributions

Please do contribute whatever you can to this module. Check out the [issues](https://github.com/burnbright/silverstripe-importexport/issues) and [milestones](https://github.com/burnbright/silverstripe-importexport/milestones) to see what has needs to be done.

## License

MIT

## Author

Jeremy Shipman (http://jeremyshipman.com)
