<?php

class biller
{    
    public static function get_all($domain_id='')
    {
        global $LANG;
        $domain_id = domain_id::get($domain_id);

        $sql = "SELECT * FROM ".TB_PREFIX."biller WHERE domain_id = :domain_id ORDER BY name";
        $sth  = dbQuery($sql,':domain_id',$domain_id);
        
        $billers = null;
        
        for($i=0;$biller = $sth->fetch();$i++) {
            
            if ($biller['enabled'] == 1) {
                $biller['enabled'] = $LANG['enabled'];
            } else {
                $biller['enabled'] = $LANG['disabled'];
            }
            $billers[$i] = $biller;
        }
        
        return $billers;
    }

    public static function select($id, $domain_id='')
    {
        global $LANG;
        $domain_id = domain_id::get($domain_id);
        
        $sql = "SELECT * FROM ".TB_PREFIX."biller WHERE domain_id = :domain_id AND id = :id";
        $sth  = dbQuery($sql,':domain_id',$domain_id, ':id',$id);
        
	$biller = $sth->fetch();
	$biller['wording_for_enabled'] = $biller['enabled']==1?$LANG['enabled']:$LANG['disabled'];
	return $biller;
        #return $sth->fetch();
    }
}
