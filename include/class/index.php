<?php

/*
Class: Index

Purpose: The index class is the replacement for primary keys as the ID field in various tables - ie. si_invoices 

Details:
$node = this is the module in question - ie 'invoice', 'products' etc..
$sub_node = the sub set of the node - ie. this is the 'invoice preference' if node = 'invoice'
$sub_node_2 = 2nd sub set of the node - ir. this is the 'biller' if node = 'invoice'

Simulate with:

SELECT
    MAX(iv.index_id) AS id
    , 'invoice' AS node
    , pr.index_group AS sub_node
    , 0 AS sub_node_2
    , iv.domain_id
FROM
    si_preferences pr INNER JOIN si_invoices iv
        ON (pr.pref_id = iv.preference_id) AND (pr.domain_id = iv.domain_id)
GROUP BY iv.domain_id, pr.index_group;

We now do not need the si_index table anymore - Ap.Muthu

*/

class index
{

	private function get_index_sql($node) {
		
		$sql = false;
		$invoice_numbering_by_biller = false;

		switch ($node) {
			case "invoice":
				$sql  = "SELECT MAX(iv.index_id) AS id";
				$sql .= ", pr.index_group AS sub_node";
				$sql .= ", " . (($invoice_numbering_by_biller) ? "iv.biller_id" : "0") . " AS sub_node_2";
				$sql .= ", iv.domain_id AS domain_id";
				$sql .= " FROM ".TB_PREFIX."preferences pr INNER JOIN ".TB_PREFIX."invoices iv ";
				$sql .= " ON (pr.pref_id = iv.preference_id) AND (pr.domain_id = iv.domain_id) ";
				$sql .= " GROUP BY domain_id, sub_node, sub_node_2 ";
				$sql .= " HAVING sub_node = :sub_node";
				$sql .= " AND sub_node_2 = :sub_node_2";
				$sql .= " AND domain_id = :domain_id";
				break;
			default:
		}
		return $sql;
	}
	
    public static function next($node, $sub_node=0, $domain_id='', $sub_node_2=0)
    {

        $domain_id = domain_id::get($domain_id);

		// node gets filtered here
        $sql = index::get_index_sql($node);
		if ($sql === false) die ("Invalid Node: $node for Invoice");


		$sth = dbQuery($sql,
			 ':sub_node', $sub_node, 
		   ':sub_node_2', $sub_node_2,
		    ':domain_id', $domain_id);

        $index = $sth->fetch();

        if($index['id'] == "") $id = 1;
        else $id = $index['id'] + 1;
        
        return $id;

    }

    public static function increment($node, $sub_node=0, $domain_id='', $sub_node_2=0)
    {
    
 		// This method is needed only if si_index table is present and being used.
       $domain_id = domain_id::get($domain_id);
        $next = index::next($node, $sub_node, $domain_id, $sub_node_2);

        return $next;

    }

    public static function rewind($node, $sub_node=0, $domain_id='', $sub_node_2=0)
    {
        $domain_id = domain_id::get($domain_id);

// This method does not seem to be used now.

    }
}

