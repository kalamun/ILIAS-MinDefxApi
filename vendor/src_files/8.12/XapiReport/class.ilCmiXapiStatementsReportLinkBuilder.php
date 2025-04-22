<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilCmiXapiStatmentsAggregateLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 * @author      Roberto Kalamun Pasini <rp@kalamun.net>
 * edited by MinDefxAPI v8.12
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReportLinkBuilder extends ilCmiXapiAbstractReportLinkBuilder
{
    /**
     * @return array<int, array<string|mixed[]>>
     */
    protected function buildPipeline(): array
    {
        $pipeline = [];
        $obj = $this->getObj();
        $params='activity='.$obj->getActivityId();

        if($this->filter->getVerb()!=''){
        	$params.='&verb='.$this->filter->getVerb();
        }
        if($this->filter->getStartDate() || $this->filter->getEndDate()) {          
            if ($this->filter->getStartDate()) {
                $params.='&since='.$this->filter->getStartDate()->toXapiTimestamp();
            }
            if ($this->filter->getEndDate()) {
                $params.='&until='.$this->filter->getEndDate()->toXapiTimestamp();
            }
        }
        if ($this->filter->getActor()){
        
        	if($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5){
        	  	$params.='&agent={"account":{"homePage":"http://'.str_replace('www.', '', $_SERVER['HTTP_HOST']).'","name":"'.$this->filter->getActor()->getUsrIdent().'"}}';
        	}
        	else{
        		$params.='&agent={"mbox":"mailto:'.$this->filter->getActor()->getUsrIdent().'"}';
        	}
        }
        if ($this->orderingField()=='dateAsc'){$params.='&ascending=true';}
                
        $pipeline=array($params.'&related_activities='.$this->buildRelatedActivities().'&limit=0');
        return $pipeline;
    }


    protected function buildActivityId(): array
    {
    	$obj = $this->getObj();
    	return $obj->getActivityId();
    }

    protected function buildRelatedActivities(): string
    {
    	return 'true';
    }
	
    public function orderingField(): string
    {
        ilObjCmiXapi::log()->debug('Dans OrderingFields');
        switch ($this->filter->getOrderField()) {
            case 'object': // definition/description are displayed in the Table if not empty => sorting not alphabetical on displayed fields
                ilObjCmiXapi::log()->debug('tri par objet');
                $column = 'objet';
                ilUtil::sendInfo("Le tri par $column n'est pas disponible");
                break;
                
            case 'verb':
            ilObjCmiXapi::log()->debug('tri par verbe');
                $column = 'verbe';
                ilUtil::sendInfo("Le tri par $column n'est pas disponible");
                break;
                
            case 'actor':
            ilObjCmiXapi::log()->debug('tri par acteur');
                $column = 'utilisateur';
                ilUtil::sendInfo("Le tri par $column n'est pas disponible");
                break;
                
            case 'date':
            	ilObjCmiXapi::log()->debug('tri par date');
            	if ($this->filter->getOrderDirection()=='asc'){
            	    	$column='dateAsc';
            	    	}
            	else {$column='dateDesc';}
            	break;
            default:
            ilObjCmiXapi::log()->debug('tri par defaut');
                $column = 'dateDesc';
                break;
        }
        
        return $column;
    }

}

