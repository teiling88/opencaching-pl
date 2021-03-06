<?php

use Utils\Database\XDb;
use Utils\Gis\Gis;
use lib\Objects\Coordinates\Coordinates;

//prepare the templates and include all neccessary

$tplname = 'region';

require_once('./lib/common.inc.php');

$lat_float = 0;
if (isset($_REQUEST['lat'])) {
    $lat_float = (float) $_REQUEST['lat'];
    $lat = $_REQUEST['lat'];
}

$lon_float = 0;
if (isset($_REQUEST['lon'])) {
    $lon_float = (float) $_REQUEST['lon'];
    $lon =  $_REQUEST['lon'];
}

$coords = Coordinates::FromCoordsFactory($lat, $lon);
tpl_set_var('coords_str', $coords->getAsText(Coordinates::COORDINATES_FORMAT_DEG_MIN));

$sCode = '';

$rsLayers = XDb::xSql(
    "SELECT `level`, `code`, AsText(`shape`) AS `geometry` FROM `nuts_layer`
    WHERE ST_WITHIN(GeomFromText( ? ), `shape`) ORDER BY `level` DESC", 'POINT(' . $lon . ' ' . $lat . ')');
while ($rLayers = XDb::xFetchArray($rsLayers)) {
    if (Gis::ptInLineRing($rLayers['geometry'], 'POINT(' . $lon . ' ' . $lat . ')')) {
        $sCode = $rLayers['code'];
        break;
    }
}
XDb::xFreeResults($rsLayers);


if ($sCode != '') {
    $adm1 = null;
    $code1 = null;
    $adm2 = null;
    $code2 = null;
    $adm3 = null;
    $code3 = null;
    $adm4 = null;
    $code4 = null;

    if (mb_strlen($sCode) > 5)
        $sCode = mb_substr($sCode, 0, 5);

    if (mb_strlen($sCode) == 5) {
        $code4 = $sCode;
        $adm4 = XDb::xMultiVariableQueryValue(
            "SELECT `name` FROM `nuts_codes` WHERE `code`=:1", 0, $sCode);
        $sCode = mb_substr($sCode, 0, 4);
    }

    if (mb_strlen($sCode) == 4) {
        $code3 = $sCode;
        $adm3 = XDb::xMultiVariableQueryValue(
            "SELECT `name` FROM `nuts_codes` WHERE `code`= :1", 0, $sCode);
        $sCode = mb_substr($sCode, 0, 3);
    }

    if (mb_strlen($sCode) == 3) {
        $code2 = $sCode;
        $adm2 = XDb::xMultiVariableQueryValue(
            "SELECT `name` FROM `nuts_codes` WHERE `code`= :1", 0, $sCode);
        $sCode = mb_substr($sCode, 0, 2);
    }

    if (mb_strlen($sCode) == 2) {
        $code1 = $sCode;

        if (Xdb::xContainsColumn('countries', 'list_default_' . $lang))
            $lang_db = $lang;
        else
            $lang_db = "en";

        $eLang = XDb::xEscape($lang_db);

        // try to get localised name first
        $adm1 = XDb::xMultiVariableQueryValue(
            "SELECT `countries`.`$eLang` FROM `countries`
            WHERE `countries`.`short`= :1 ", 0, $sCode);

        if ($adm1 == null)
            $adm1 = XDb::xMultiVariableQueryValue(
                "SELECT `name` FROM `nuts_codes` WHERE `code`= :1 ", 0, $sCode);
    }
    tpl_set_var('country', $adm1);
    tpl_set_var('region', $adm3);
} else {
    tpl_set_var('country', "");
    tpl_set_var('region', "");
}

//From Google

$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lon . '&amp;key=' . $googlemap_key . '&amp;language=' . $lang;
$data = @file_get_contents($url);

$jsondata = json_decode($data, true);

//print_r($jsondata);

if (isset($jsondata['status']) && strtoupper($jsondata['status']) == 'OK') {
    $woj = $jsondata['results']['0']['address_components']['4']['long_name'];
    tpl_set_var('region_gm', $woj);
} else {

    tpl_set_var('region_gm', '');
}

//make the template and send it out
tpl_BuildTemplate();
