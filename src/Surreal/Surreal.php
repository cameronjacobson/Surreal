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
			return self::serializeObject($anything);
		}
	}

	private static function serializeObject($obj){
		return 'O:'.strlen(get_class($obj)).':"'.get_class($obj).'":'.count($obj).':{'.self::serializeObjectProperties($obj).'}';
	}

	private static function serializeObjectProperties($obj){
		$rc = new \ReflectionClass($obj);
		$return = '';
		$classname = get_class($obj);
		foreach($obj as $k=>$v){
			try{
				$prop = $rc->getProperty($k);
				$prop->setAccessible(true);
				if($prop->isPublic()){
					$return .= self::surrealize($prop->getName());
					$return .= self::surrealize($prop->getValue($obj));
				}
				else if($prop->isProtected()){
					$name = pack('C',0x00).'*'.pack('C',0x00).$prop->getName();
					$return .= 's:'.strlen($name).':"'.$name.'";';
					$return .= self::surrealize($prop->getValue($obj));
				}
				else if($prop->isPrivate()){
					$name = pack('C',0x00).$classname.pack('C',0x00).$prop->getName();
					$return .= 's:'.strlen($name).':"'.$name.'";';
					$return .= self::surrealize($prop->getValue($obj));
				}
				else if($prop->isStatic){
					$return .= self::surrealize($prop->getName());
					$return .= self::surrealize($prop->getValue($obj));
				}
			}
			catch(\ReflectionException $e){
				$return .= self::surrealize($k);
				$return .= self::surrealize($v);
			}
		}
		return $return;
	}
}

