<?php
namespace Test\Track;

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Surreal\Surreal;

class Blah
{
	private $blah1 = 1;
	protected $blah2 = 2;
	public $blah3 = 3;
	public static $blah4 = 4;
	protected static $blah5 = 5;
	private static $blah6 = 6;
	public $blah7;

	public function abc(){
		var_dump(self::$blah5);
		var_dump(self::$blah6);
	}
	public function setit(){
		self::$blah5 = 99;
		self::$blah6 = 101;
	}
}

$blah = new Blah();
$blah->blah4 = 33;
$blah->blah7 = new Blah();
$blah->setit();

$values = array(
	1,
	2.0,
	5.5,
	"string123",
	true,
	false,
	array(1,2,3,4,5),
	array('a'=>1,'b'=>2,'c'=>3),
	(object)array('rga'=>1,'grb'=>2,'cdc'=>3),
	$blah,
);

foreach($values as $key => $value){
	echo 'TEST#: '.$key.PHP_EOL;

	$start = microtime(true);
	$ser = serialize($value);
	echo ' '.(microtime(true) - $start).PHP_EOL;

	$start = microtime(true);
	$ser = Surreal::surrealize($value);
	echo ' '.(microtime(true) - $start).PHP_EOL;
var_dump($ser);
	var_dump($gg = unserialize($ser));
}
$gg->abc();
