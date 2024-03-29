<PRE><?php
/**
 * Created by Ari Asulin.
 * Date: 8/6/12
 * Time: 10:13 AM
 */

define("LIBPATH", getcwd() .'/jszipcode');
define("INDEX_ZIPS", 0);
define("INDEX_CITIES", 1);
define("INDEX_STATES", 2);


$pdo = new PDO(
    'mysql:host=localhost;dbname=jszip',
    'test',
    'test');

//$sql = "SELECT `zi_zipcode` zip, `ci_name` city, `st_name` state, `st_short` short FROM `zipjoin` limit 20";
$sql_group = "SELECT ROUND( zi_zipcode / 100 ) AS grp FROM  `zipjoin` GROUP BY grp ORDER BY grp ASC LIMIT 0 , 30";

$sql_zips = "SELECT `zi_zipcode` zip, `ci_name` city, `st_name` state, `st_short` short FROM zipjoin
    WHERE zi_zipcode between :min and :max ";

$sth_zips = $pdo->prepare($sql_zips);
foreach ($pdo->query($sql_group) as $grp)
{
    $params = array(':min' => intval($grp['grp']) * 100 , ':max' => (intval($grp['grp'])+1) * 100 );
    $result = $sth_zips->execute($params);
    $json = array();
    $stateLookup = array();
    $cityLookup = array();
    $stCounter = 0;
    $ctCounter = 0;
    while ($zip = $sth_zips->fetch(PDO::FETCH_ASSOC))
    {
        if(!isset($stateLookup[$zip['short']]))
        {
            $stid = $stCounter++;
            $stateLookup[$zip['short']] = array($stid, $zip['short'].'|'.$zip['state']);
        }
        else $stid = $stateLookup[$zip['short']];

        if(!isset($cityLookup[$zip['city']]))
        {
            $ctid = $ctCounter++;
            $cityLookup[$zip['city']] = array($ctid, $stid);
        }
        else $ctid = $cityLookup[$zip['city']][0];

        print_r($zip);
        $json[INDEX_ZIPS][$zip['zip'] % 100] = $ctid;
    }
    foreach($cityLookup as $city => $data)
        $json[INDEX_CITIES][$data[0]] = $city.'|'.$data[1];
    foreach($stateLookup as $short => $data)
        $json[INDEX_STATES][$data[0]] = $data[1];

    print_r($json);
}