<?php
namespace Weide\AldocBundle;

class AldocException extends \Exception
{
    const ERR_NO_PRODUCTS_FOR_LICENSE_PLATE = 1;
    const ERR_UNKNOWN_LICENSEPLATE = 2;
    const ERR_INVALID_REQUEST = 3;
}