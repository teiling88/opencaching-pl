<?php

use lib\Controllers\MeritBadgeController;
use Utils\Database\OcDb;

require_once('./lib/common.inc.php');



if ($usr == false) {
    $target = urlencode(tpl_get_current_page());
    tpl_redirect('login.php?target=' . $target);
    exit;
}

$usrid = -1;

if (isset($_REQUEST['user_id'])) {
    $userid = $_REQUEST['user_id'];
} else {
    $userid = $usr['userid'];
}

$badge_id = $_REQUEST['badge_id'];
$show = $_REQUEST['show'];


$meritBadgeCtrl = new \lib\Controllers\MeritBadgeController;
$tmp_badge_map = "tmp_badge_map";

$gainedPositions = $meritBadgeCtrl->buildArrayGainedPositions($userid, $badge_id);
$belongingPositions = $meritBadgeCtrl->buildArrayBelongingPositions($userid, $badge_id);

$gainedList = getCachesList($gainedPositions);
$belongingList = getCachesList($belongingPositions); //TODO nawet zaarchiwizowane ? (brakuje caches.status = 1)

$db = OcDb::instance();
addCachesToTmpTable( $db, $tmp_badge_map, $show, $gainedList, $belongingList);


$borderQuery = "SELECT MAX(caches.longitude) AS maxlongitude, MAX(caches.latitude) AS maxlatitude,
MIN(caches.longitude) AS minlongitude, MIN(caches.latitude) AS minlatitude
FROM $tmp_badge_map
join caches on caches.cache_id = tmp_badge_map.cache_id";

$stmt= $db->simpleQuery($borderQuery);
$r = $db->dbResultFetchOneRowOnly($stmt);
$minlat = $r['minlatitude'];
$minlon = $r['minlongitude'];
$maxlat = $r['maxlatitude'];
$maxlon = $r['maxlongitude'];


$cacheQuery = "SELECT cache_id FROM $tmp_badge_map";
$stmt = $db->simpleQuery($cacheQuery);
$hash = uniqid();
$f = fopen($dynbasepath . "searchdata/" . $hash, "w");
while ($r = $db->dbResultFetch($stmt)) {
    fprintf($f, "%s\n", $r['cache_id']);
}
fclose($f);

tpl_redirect("cachemap3.php?userid=$userid&searchdata=$hash&fromlat=$minlat&fromlon=$minlon&tolat=$maxlat&tolon=$maxlon");


function getCachesList($positions){
    $valQuery = "";
            
    foreach( $positions as $onePos ){
        if ( $valQuery != "" )
            $valQuery .= ",";
            
        $valQuery .= "(". $onePos->getId() . ")";
    }
    
    return $valQuery;
}


function addCachesToTmpTable( $db, $tmp_badge_map, $show, $gainedList, $belongingList ){
    
    $query = "CREATE TEMPORARY TABLE $tmp_badge_map(cache_id int(11)) ENGINE=MEMORY";
    $db->simpleQuery($query);

    $insQuery = "INSERT INTO $tmp_badge_map values ";
    $delGainedQuery = "DELETE FROM $tmp_badge_map WHERE cache_id in (" . $gainedList. ")";
    
    //N - not gained
    //Y - gained
    
    if ( !(strpos($show, 'N') === false) ){ //not gained
        $db->simpleQuery($insQuery . $belongingList);
    }
    
    if ( strpos($show, 'Y') === false){ //only not gained
        $db->simpleQuery($delGainedQuery);
    }
    else { //gained
        $db->simpleQuery($insQuery . $gainedList);
    }
}

