<?php


namespace Nanatty32\HubtelMerchantAccount\Helpers;


trait FormatsRequests
{
    public function toJson($object)
    {
        $json = array();
        if(!is_object($object))
        {
            throw new InvalidArgumentException('toJson formats only objects');
        }
        foreach(get_object_vars($object) as $property => $value )
        {
            $json[$property] = $value;
        }
        return json_encode($json);
    }

    public function flatten($array)
    {
        $flattened = array();
        if(!is_array($array))
        {
            throw new InvalidArgumentException('flatten works with arrays only!');
        }
        foreach ($array as $value) {
            if(!is_array($value))
            {
                if(!is_null($value)) $flattened[] = $value;
            }else{
                $flattened = array_merge($flattened,$this->flatten($value));
            }
        }
        return $flattened;
    }
}

