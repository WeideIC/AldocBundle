### Step 1) Get the bundle

Add the following to your composer.json

    "require": {
        "weide/aldocbundle": "dev-master"
    }

### Step 2) Register the bundle

To start using the bundle, register the following bundles in your Kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Weide\AldocBundle\WeideAldocBundle(),
        // ...
    );
}
```

### Step 3) Configuration

You must configure the following setting in your config.yml:

    weide_aldoc:
        customername: weide

This is needed to generate a URL for the Aldoc webservice.

### Step 4) Profit

Use the bundle as follows:

```php
public function myAction()
{
    $aldoc = $this->get('weide_aldoc.aldoc');
    $aldoc->getTypes('08-TT-NP'); // Return types for a license plate
    $aldoc->getPartsFromType($type); // Return part numbers for a Weide\AldocBundle\Model\Type-object

    // Or all at once:
    $aldoc->getPartsFromLicensePlate('08-TT-NP');
}
```

Please note that WeideAldocBundle maintains a cache under app/cache/prod/aldoc.
Clearing your cache means that all requested license plates and parts need to be re-fetched.