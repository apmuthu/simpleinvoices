<?php
header("Content-type: text/xml");

$start = (isset($_POST['start'])) ? $_POST['start'] : "0" ;
$dir = (isset($_POST['sortorder'])) ? $_POST['sortorder'] : "ASC" ;
$sort = (isset($_POST['sortname'])) ? $_POST['sortname'] : "id" ;
$limit = (isset($_POST['rp'])) ? $_POST['rp'] : "25" ;
$page = (isset($_POST['page'])) ? $_POST['page'] : "1" ;

//SC: Safety checking values that will be directly subbed in
if (intval($page) != $page) {
	$start = 0;
}
$start = (($page-1) * $limit);

if (intval($limit) != $limit) {
	$limit = 25;
}
if (!preg_match('/^(asc|desc)$/iD', $dir)) {
	$dir = 'DESC';
}


$query = $_POST['query'];
$qtype = $_POST['qtype'];

$where = "";
if ($query) $where .= " :qtype LIKE '%:query%' ";



/*Check that the sort field is OK*/
$validFields = array('id', 'name', 'value','enabled');

if (in_array($sort, $validFields)) {
	$sort = $sort;
} else {
	$sort = "id";
}

	$sql = "SELECT 
				v.id as id, 
				a.name as name,
                v.value as value,
                v.enabled as enabled
			FROM 
				".TB_PREFIX."products_attributes a LEFT JOIN
				".TB_PREFIX."products_values v ON (a.id = v.attribute_id)
			WHERE
				$where
			ORDER BY 
				$sort $dir 
			LIMIT 
				$start, $limit";

	if ($query) {
		$sth = dbQuery($sql, ':query', $query, ':qtype', $qtype);
	} else {
		$sth = dbQuery($sql);
	}

	$customers = $sth->fetchAll(PDO::FETCH_ASSOC);
/*
	$customers = null;

	for($i=0; $customer = $sth->fetch(PDO::FETCH_ASSOC); $i++) {
		if ($customer['enabled'] == 1) {
			$customer['enabled'] = $LANG['enabled'];
		} else {
			$customer['enabled'] = $LANG['disabled'];
		}
*/

$sqlTotal = "SELECT count(id) AS count FROM ".TB_PREFIX."products_values";
$tth = dbQuery($sqlTotal);
$resultCount = $tth->fetch();
$count = $resultCount[0];
//echo sql2xml($customers, $count);
$xml .= "<rows>";

$xml .= "<page>$page</page>";

$xml .= "<total>$count</total>";

foreach ($customers as $row) {

	$xml .= "<row id='".$row['id']."'>";

	$xml .= "<cell><![CDATA[<a class='index_table' title='$LANG[view] ".$row['description']."' href='index.php?module=product_value&view=details&action=view&id=".$row['id']."&action=view'><img src='images/common/view.png' height='16' border='-5px' padding='-4px' valign='bottom' /></a> <a class='index_table' title='$LANG[edit] ".$row['description']."' href='index.php?module=product_value&view=details&action=edit&id=".$row['id']."&action=edit'><img src='images/common/edit.png' height='16' border='-5px' padding='-4px' valign='bottom' /></a>]]></cell>";

	$xml .= "<cell><![CDATA[".$row['id']."]]></cell>";		

	$xml .= "<cell><![CDATA[".utf8_encode($row['name'])."]]></cell>";
	$xml .= "<cell><![CDATA[".utf8_encode($row['value'])."]]></cell>";
	if ($row['enabled']=='1') {
		$xml .= "<cell><![CDATA[<img src='images/common/tick.png' alt='".$row['enabled']."' title='".$row['enabled']."' />]]></cell>";				
	}	
	else {
		$xml .= "<cell><![CDATA[<img src='images/common/cross.png' alt='".$row['enabled']."' title='".$row['enabled']."' />]]></cell>";				
	}


	$xml .= "</row>";		

}



$xml .= "</rows>";

echo $xml;

?> 
