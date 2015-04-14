<?php

namespace Surreal;

use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionFunction;
use \ReflectionException;

class Surreal
{
	private static $callback;
	private static $replace_callback;
	public static $cb_flag = '#';
	public static $obj_flag = '@';

	public static function setCallback(callable $cb){
		if(self::isValidCallback($cb, 2)){
			self::$callback = $cb;
			return true;
		}
		return false;
	}

	public static function setReplaceCallback(callable $cb){
		if(self::isValidCallback($cb, 1)){
			self::$replace_callback = $cb;
			return true;
		}
		return false;
	}

	private static function isValidCallback($callback, $numargs){
		$rf = new ReflectionFunction($callback);
		if($rf->getNumberOfParameters() !== 2){
			return false;
		}
		return true;
	}

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
			if((count($anything) === 1) && (key($anything)[0] === self::$obj_flag)){
				return self::surrealizeArray2Object(substr(key($anything),1),$anything[key($anything)]);
			}
			else if((count($anything) === 1)
			 && (key($anything)[0] === self::$replace_flag)
			 && !empty(self::$replace_callback)
			 && is_callable(self::$replace_callback)){
				$anything = self::$replace_callback(substr(key($anything),1),$anything[key($anything)]);
				return self::surrealize($anything);
			}
			else{
				$stuff = 'a:'.count($anything).':{';
				foreach($anything as $k=>$v){
					if(($k[0] === self::$cb_flag)
					 && !empty(self::$callback)
					 && is_callable(self::$callback)){
						list($k,$v) = self::$callback($k,$v);
					}
					$stuff .= self::surrealize($k);
					$stuff .= self::surrealize($v);
				}
				return $stuff.'}';
			}
		}
		else if(is_object($anything)){
			return self::surrealizeObject($anything);
		}
	}

	private static function surrealizeArray2Object($classname, array $arr){
		$rc = new ReflectionClass($classname);
		$obj = $rc->newInstanceWithoutConstructor();
		foreach($arr as $k=>$v){
			try{
				$prop = new ReflectionProperty($classname,$k);
				$prop->setAccessible(true);
				$prop->setValue($obj,$v);
			}catch(ReflectionException $e){
				$obj->$k = $v;
			}
		}
		$val = self::surrealizeObject($obj);
		return $val;
	}

	private static function surrealizeObject($obj){
		return implode(':',array(
			'O',
			strlen(get_class($obj)),
			'"'.get_class($obj).'"',
			count($obj),
			'{'.self::surrealizeObjectProperties($obj).'}'
		));
	}

	private static function surrealizeObjectProperties($obj){
		$rc = new \ReflectionClass($obj);
		$return = '';
		$classname = get_class($obj);
		$props = $rc->getProperties();
		foreach($props as $k=>$prop){
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
		return $return;
	}
}

