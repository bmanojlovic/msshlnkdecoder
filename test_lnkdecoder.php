<?php
include "lnkdecoder.class.php";

// http://www.php.net/manual/en/function.chr.php#89488
function echocolor($text,$color="normal",$back=0)
{
  $colors = array('light_red'  => "[1;31m", 'light_green' => "[1;32m", 'yellow'     => "[1;33m",
                  'light_blue' => "[1;34m", 'magenta'     => "[1;35m", 'light_cyan' => "[1;36m",
                  'white'      => "[1;37m", 'normal'      => "[0m",    'black'      => "[0;30m",
                  'red'        => "[0;31m", 'green'       => "[0;32m", 'brown'      => "[0;33m",
                  'blue'       => "[0;34m", 'cyan'        => "[0;36m", 'bold'       => "[1m",
                  'underscore' => "[4m",    'reverse'     => "[7m" );
  $out = $colors["$color"];
  $ech = chr(27)."$out"."$text".chr(27)."[0m";
  if($back)
  {
    return $ech;
  }
    else
  {
    echo $ech . PHP_EOL;
  }
}

function failed () {
  echocolor(" FAILED",$color="red",$back=0);
}

function passed () {
  echocolor(" PASSED",$color="green",$back=0);
}

function separator () {
  echocolor ("          =====", $color="magenta",$back=0);
}


//$test_files = array('paint.zip.lnk','winhelp.maximized.lnk');
//$test_files = array('IO.SYS.lnk');
$msshlnk = array();
//$FOLDER = 'samples';
$FOLDER = 'lnk_files';
//$test_files = new DirectoryIterator($FOLDER);
$test_files = array('mysqlwb.lnk','named-file.lnk','remote-disk-sdelete.exe.lnk','Notepad.lnk');
//$test_files = array('mysqlwb.lnk','named-file.lnk');

echo "======== Test Suite for MSSHLNK =======" . PHP_EOL;
echo "=======================================" . PHP_EOL;
echo "             Opening Files" . PHP_EOL;
echo "=======================================" . PHP_EOL;

foreach ($test_files as $file) { 
  //
  $file_fixed = str_replace(' ','_',$file);
  if ($file == '.' || $file == '..') continue; 
  echo "Opening lnk file \"" . $FOLDER . DIRECTORY_SEPARATOR . $file . '"... ' ; 
  $msshlnk[$file_fixed] = new MSshlnk();
  if (!$msshlnk[$file_fixed]->open($FOLDER . DIRECTORY_SEPARATOR . $file)) {
    failed() . PHP_EOL;
    echo "      errno="
        . $msshlnk[$file_fixed]->errno
        . ' errstring="'
        . $msshlnk[$file_fixed]->errstring 
        . '"' . PHP_EOL;
    unset($msshlnk[$file_fixed]);
  } else {
     passed() . PHP_EOL;
  }
  echo PHP_EOL;
}



foreach($msshlnk as $key => $lnk) {
  echo PHP_EOL;
  echo PHP_EOL;
  echo PHP_EOL;
  echo "=======================================" . PHP_EOL;
  echo "        ===================            " . PHP_EOL;
  echo "  parse_LinkInfo for $key" . PHP_EOL;
  echo "        ===================            " . PHP_EOL;
  $lnk->parse();
//  echo "LinkFlags= "; print_r($lnk->LinkFlags);
  echo "StructSize= "; print_r($lnk->StructSize);
  echo "ParsedInfo= "; @print_r($lnk->ParsedInfo);
echo "=======================================" . PHP_EOL;
}


//var_dump($msshlnk['winhelp.maximized.lnk']);



//print_r( str_split($msshlnk[$key]->lnk_bin));

echo "========== End of test suite ==========" . PHP_EOL;
?>