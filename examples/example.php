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
}

$blah = new Blah();
$blah->blah4 = 33;
$blah->blah7 = new Blah();

//echo serialize($blah);
//exit;
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

foreach($values as $value){
	$ser = Surreal::surrealize($value);
	echo $ser.PHP_EOL;
	var_dump($gg = unserialize($ser));
}

//var_dump($gg::$blah4);
