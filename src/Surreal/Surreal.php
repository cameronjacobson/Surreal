<?php

namespace Surreal;

use \ReflectionClass;
use \ReflectionProperty;

class Surreal
{
	public static function surrealize($anything){
		if(is_null($anything)){
			return 'N;';
		}
		else if(is_int($anything)){
			return 'i:'.$anything.';';
		}
		else if(is_bool($anything)){
			return 'b:'.(int)$anything.';';
		}
		else if(is_float($anything)){
			return 'd:'.$anything.';';
		}
		else if(is_string($anything)){
			return 's:'.strlen($anything).':"'.$anything.'";';
		}
		else if(is_array($anything)){
			$stuff = 'a:'.count($anything).':{';
			foreach($anything as $k=>$v){
				$stuff .= self::surrealize($k);
				$stuff .= self::surrealize($v);
			}
			return $stuff.'}';
		}
		else if(is_object($anything)){
			if(get_class($anything) == 'stdClass'){
				return self::surrealize((array)$anything);
			}
			return self::serializeObject($anything);
		}
	}

	private static function serializeObject($obj){
		$rc = new \ReflectionClass($obj);
		$props = $rc->getProperties();
		return 'O:'.strlen(get_class($obj)).':"'.get_class($obj).'":'.count($props).':{'.self::serializeObjectProperties($obj,$props).'}';
	}

	private static function serializeObjectProperties($obj, $props){
		$return = '';
		$classname = get_class($obj);
		foreach($props as $prop){
			$prop->setAccessible(true);
			if($prop::IS_STATIC){
				$return .= self::surrealize($prop->getName());
				$return .= self::surrealize($prop->getValue($obj));
			}
			else if($prop::IS_PUBLIC){
				$return .= self::surrealize($prop->getName());
				$return .= self::surrealize($prop->getValue($obj));
			}
			else if($prop::IS_PROTECTED){
				$return .= self::surrealize($prop->getName());
				$return .= pack('c',0x00).'*'.pack('c',0x00).self::surrealize($prop->getValue($obj));
			}
			else if($prop::IS_PRIVATE){
				$return .= self::surrealize($prop->getName());
				$return .= pack('c',0x00).$classname.pack('c',0x00).self::surrealize($prop->getValue($obj));
			}
		}
		return $return;
	}
}

