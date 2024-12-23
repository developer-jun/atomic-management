<?php

function _g($text, $domain = Domain\Constants::TEXT_DOMAIN) {
  return __($text, $domain);
}

function select_field_data_format($objects, $property_keys) {
  return array_map(function($object) use ($property_keys) {
      $array = [];
      foreach ($property_keys as $key => $property) {        
          $array[$key] = $object->$property;
      }
      return $array;
  }, $objects);
}