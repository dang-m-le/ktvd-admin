<?php

$uc2ascii = array(
    0X0041=>"A",0X00C1=>"A",0X00C0=>"A",0X1EA2=>"A",0X00C3=>"A",0X1EA0=>"A",
    0X00C2=>"A",0X1EA4=>"A",0X1EA6=>"A",0X1EA8=>"A",0X1EAA=>"A",0X1EAC=>"A",
    0X0102=>"A",0X1EAE=>"A",0X1EB0=>"A",0X1EB2=>"A",0X1EB4=>"A",0X1EB6=>"A",
    0X0045=>"E",0X00C9=>"E",0X00C8=>"E",0X1EBA=>"E",0X1EBC=>"E",0X1EB8=>"E",
    0X00CA=>"E",0X1EBE=>"E",0X1EC0=>"E",0X1EC2=>"E",0X1EC4=>"E",0X1EC6=>"E",
    0X0049=>"I",0X00CD=>"I",0X00CC=>"I",0X1EC8=>"I",0X0128=>"I",0X1ECA=>"I",
    0X004F=>"O",0X00D3=>"O",0X00D2=>"O",0X1ECE=>"O",0X00D5=>"O",0X1ECC=>"O",
    0X00D4=>"O",0X1ED0=>"O",0X1ED2=>"O",0X1ED4=>"O",0X1ED6=>"O",0X1ED8=>"O",
    0X01A0=>"O",0X1EDA=>"O",0X1EDC=>"O",0X1EDE=>"O",0X1EE0=>"O",0X1EE2=>"O",
    0X0055=>"U",0X00DA=>"U",0X00D9=>"U",0X1EE6=>"U",0X0168=>"U",0X1EE4=>"U",
    0X01AF=>"U",0X1EE8=>"U",0X1EEA=>"U",0X1EEC=>"U",0X1EEE=>"U",0X1EF0=>"U",
    0X0059=>"Y",0X00DD=>"Y",0X1EF2=>"Y",0X1EF6=>"Y",0X1EF8=>"Y",0X1EF4=>"Y",
    0X0044=>"D",0X0110=>"D",
    0x0061=>"a",0x00e1=>"a",0x00e0=>"a",0x1ea3=>"a",0x00e3=>"a",0x1ea1=>"a",
    0x00e2=>"a",0x1ea5=>"a",0x1ea7=>"a",0x1ea9=>"a",0x1eab=>"a",0x1ead=>"a",
    0x0103=>"a",0x1eaf=>"a",0x1eb1=>"a",0x1eb3=>"a",0x1eb5=>"a",0x1eb7=>"a",
    0x0065=>"e",0x00e9=>"e",0x00e8=>"e",0x1ebb=>"e",0x1ebd=>"e",0x1eb9=>"e",
    0x00ea=>"e",0x1ebf=>"e",0x1ec1=>"e",0x1ec3=>"e",0x1ec5=>"e",0x1ec7=>"e",
    0x0069=>"i",0x00ed=>"i",0x00ec=>"i",0x1ec9=>"i",0x0129=>"i",0x1ecb=>"i",
    0x006f=>"o",0x00f3=>"o",0x00f2=>"o",0x1ecf=>"o",0x00f5=>"o",0x1ecd=>"o",
    0x00f4=>"o",0x1ed1=>"o",0x1ed3=>"o",0x1ed5=>"o",0x1ed7=>"o",0x1ed9=>"o",
    0x01a1=>"o",0x1edb=>"o",0x1edd=>"o",0x1edf=>"o",0x1ee1=>"o",0x1ee3=>"o",
    0x0075=>"u",0x00fa=>"u",0x00f9=>"u",0x1ee7=>"u",0x0169=>"u",0x1ee5=>"u",
    0x01b0=>"u",0x1ee9=>"u",0x1eeb=>"u",0x1eed=>"u",0x1eef=>"u",0x1ef1=>"u",
    0x0079=>"y",0x00fd=>"y",0x1ef3=>"y",0x1ef7=>"y",0x1ef9=>"y",0x1ef5=>"y",
    0x006d=>"d",0x0111=>"d"
);

//
function vn2us($s)
{
    global $uc2ascii;

    $res = "";
    $len = strlen($s);
    for ($i=0; $i<$len; ++$i) {
    $h = ord($s[$i]);
    if ($h <= 0x7F) {
        ;
    }
    else if ($h < 0xC2) {
        ;
    }
    else if ($h <= 0xDF) {
        $h = ($h & 0x1F) << 6 | (ord($s[++$i]) & 0x3F);
    }
    else if ($h <= 0xEF) {
        $h = ($h & 0x0F) << 12 | (ord($s[++$i]) & 0x3F) << 6
        | (ord($s[++$i]) & 0x3F);
    }
    else {
        $h = ($h & 0x0F) << 18 | (ord($s[++$i]) & 0x3F) << 12
	| (ord($s[++$i]) & 0x3F) << 6
        | (ord($s[++$i]) & 0x3F);
    }
    if ($h < 0x80) {
	$res .= chr($h);
    }
    else {
        $res .= $uc2ascii[$h];
    }
    }
    return $res;
}

?>