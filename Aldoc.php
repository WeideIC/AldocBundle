<?php
namespace Weide\AldocBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Weide\AldocBundle\Model\Type;
use Weide\AldocBundle\Model\Part;
use Weide\AldocBundle\CacheResolver\TypeCacheResolver;
use Weide\AldocBundle\CacheResolver\PartsCacheResolver;
use Intrepidity\LicensePlate\DutchLicensePlate;

class Aldoc
{
    private $menucode;
    private $tcr;
    private $pcr;
    private $container;

    public function __construct(ContainerInterface $container, PartsCacheResolver $pcr, TypeCacheResolver $tcr)
    {
        $this->tcr = $tcr;
        $this->pcr = $pcr;
        $this->container = $container;
        $this->menucode = $this->container->getParameter('weide_aldoc.defaultmenucode');
    }

    /**
     * Get available car makes and models from a Dutch license plate number
     * @param string $licenseplate Dutch license plate string, such as "5-ABC-78"
     * @return array
     */
    public function getTypes($licenseplate)
    {
        $lp = new DutchLicensePlate($licenseplate);
        if(!$lp->isValid())
        {
            throw new AldocException(AldocException::ERR_UNKNOWN_LICENSEPLATE);
        }

        $key = $this->tcr->generateKey(array('licenseplate' => $licenseplate));
        if(!$this->tcr->isCached($key)) {
            $curl = $this->constructRequest("type", "get", array('kenteken' => $licenseplate));
            $xml = new \SimpleXMLElement(curl_exec($curl));
        } else  {
            $xml = new \SimpleXMLElement($this->tcr->getCached($key));
        }
        $root = $xml->xpath("/Type");

        $types = array();
        foreach($root as $t)
        {
            $type = new Type();
            $type->carcode =    (int)    $t->carcode;
            $type->make =       (string) $t->carmake;
            $type->modelcode =  (int)    $t->modelcode;
            $type->model =      (string) $t->model;
            $type->modelrem =   (string) $t->modrem;
            $type->yearStart = (int)    substr((string) $t->modbegin, 2);
            $type->yearEnd =   (int)    substr((string) $t->einde, 2);
            $type->typeCode =   (int)    $t->typecode;
            $type->type =       (string) $t->type;
            $type->motorType =  (string) $t->motortype;
            $type->fuel =       (string) $t->brandstof;
            $type->power =      (int)    $t->vermogen;
            $type->power2 =     (int)    $t->vermogen2;

            $types[] = $type;
        }

        if(sizeof($types) == 0 || trim($types[0]->make) == "")
        {
            throw new AldocException(AldocException::ERR_UNKNOWN_LICENSEPLATE);
        }

        return $types;

    }

    public function getPartsFromType($typecode, $menucode = null)
    {
        if(is_null($menucode))
        {
            $menucode = $this->menucode;
        }
        $key = $this->pcr->generateKey(array(
            'typecode' => $typecode,
            'menucode' => $menucode
        ));
        if (!$this->pcr->isCached($key)) {
            $curl = $this->constructRequest("parts", "get", array('typecode' => $typecode, 'menucode' => $menucode));
            $xml = new \SimpleXMLElement(curl_exec($curl));
        } else {
            $xml = new \SimpleXMLElement($this->pcr->getCached($key));
        }
        $root = $xml->xpath("/Parts/Part");

        $parts = array();
        foreach($root as $p)
        {
            $part = new Part();
            $part->article =        (int)    $p->art;
            $part->part =       (string) $p->part;
            $part->sup =        (string) $p->sup;
            $part->refrem =     (string) $p->refrem;
            $part->prijs =      (int)    $p->prijs;
            $part->parent =     (int)    $p->parent;
            $part->partcode =   (int)    $p->partcode;
            $part->supcode =    (int)    $p->supcode;
            $parts[] = $part;
        }

        return $parts;
    }

    public function getPartsFromLicensePlate($licenseplate, $menucode = null)
    {
        // Search types for license plate
        $types = $this->getTypes($licenseplate);
        $parts = array();
        if($types !== false)
        {
            foreach($types as $t)
            {
                // Search parts for this type
                $p = $this->getPartsFromType($t->typecode, $menucode);
                if(is_array($p))
                {
                    foreach($p as $p2)
                    {
                        $parts[] = $p2->art;
                    }
                }
            }
        }

        $type = "";
        if(sizeof($types) > 0)
        {
            $type = $types[0]->make . " " . $types[0]->model;
        }

        $result = array(
            'type' => $type,
            'parts' => $parts
        );

        return $result;
    }

    private function constructRequest($pagename, $request_type = 'get', $kvpairs = array())
    {
        if(!in_array($request_type, array('get', 'post')))
        {
            throw new AldocException(AldocException::ERR_INVALID_REQUEST);
        }

        $kvpstring = "";
        foreach($kvpairs as $key => $value)
        {
            $kvpstring .= "&{$key}={$value}";
        }
        $kvpstring = substr($kvpstring, 1);

        $url = "";
        if(strlen($kvpstring) > 0)
        {
            $url = "http://" . $this->container->getParameter('weide_aldoc.customername') . ".aldoc.eu/ws/" . $this->container->getParameter('weide_aldoc.customername') . "/{$pagename}.alx?{$kvpstring}";
            $curl = curl_init($url);
        }
        else
        {
            $url = "http://" . $this->container->getParameter('weide_aldoc.customername') . ".aldoc.eu/ws/" . $this->container->getParameter('weide_aldoc.customername') . "/{$pagename}.alx";
            $curl = curl_init($url);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));

        if($request_type == 'post')
        {
            curl_setopt($curl, CURLOPT_POST, true);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        return $curl;
    }
}