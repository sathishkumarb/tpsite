<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class EmailRepository extends EntityRepository{        
    
    public function getEmailListing($sqlArr, $editUrl, $basePath){
        $columnArr = ['u.subject'];
        $sortByColumnName  =    $columnArr[$sqlArr['sortcolumn']];
        $searhKey          =    $sqlArr['searchKey'];        
        $sortType          =    $sqlArr['sorttype'];
        $offSet            =    $sqlArr['iDisplayStart'];
        $sEcho             =    $sqlArr['sEcho'];
        $limit             =    $sqlArr['limit'];       
		
        $offSet = (int)$offSet;
        $limit =(int) $limit;        
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Email', 'u');
        $query  = $query->select('u.id, u.subject, u.isActive');						
        $searchStr='';
        if(trim($searhKey) !=''){ 
            $searhKey = str_replace("<br>",'',trim($searhKey));               
            $query = $query->where('u.subject LIKE :emailsub')				
                           ->setParameter('emailsub', '%'.$searhKey.'%');
        }                 

        $query = $query->getQuery();      
        $Result = $query->getResult();	        
        $totalRecordCount =  count($Result);
        $recArr[] = array();        
        $query = '';
        $query = $em->createQueryBuilder('u');
        $sortType = strtoupper($sortType);
        
        if($sortType == "ASC"){
            $sortType ='ASC';
        } else {  
            $sortType ='DESC'; 
        } 
        $query = $query->from('Admin\Entity\Email', 'u');
        $searchStr='';
        
        $query  = $query->select('u.id, u.subject, u.isActive');      
        $searchStr='';
        if(trim($searhKey) !=''){ 
            $searhKey = str_replace("<br>",'',trim($searhKey));               
            $query = $query->where('u.subject LIKE :emailsub')				
                            ->setParameter('emailsub', '%'.$searhKey.'%');
        }        
        $query = $query->setMaxResults($limit);
        $query = $query->setFirstResult($offSet);
        $query = $query->orderBy($sortByColumnName, $sortType);		
        $query = $query->getQuery();
        $Result = $query->getResult();
        $key=0;
        $EmailDataList['sEcho']  =   $sEcho;
        $EmailDataList['iTotalRecords']         =    $totalRecordCount;
        $EmailDataList['iTotalDisplayRecords']  =    $totalRecordCount;
        $title = '';
        $imageShow = '';
        $width = '';
        $height = '';                
        foreach ($Result  as $emailRow) {              
            $emailid = $id = $emailRow['id'];            
            $EmailDataList['aaData'][$key][0]  =  $emailRow['subject'];                
            $EmailDataList['aaData'][$key][1]  =  ($emailRow['isActive'] == 1 ? 'Active' : 'InActive');            
            $edit_url = $basePath."/admin/email/edit/".$emailid;
            $EmailDataList['aaData'][$key][2]  =   "<a href= '".$edit_url."' title='Edit Email Template'><i class='icon-edit'></i></span></a>&nbsp;";
            $key++;	
        }
        if($key == 0){ $EmailDataList['aaData'] = ''; }
        return $EmailDataList;
    }

    public function getEmailById($emailid){     
        $em = $this->getEntityManager();
	$query = $em->createQueryBuilder('u');
	$query = $query->from('Admin\Entity\Email', 'u');
	$query = $query->select('u.subject, u.content, u.keydata, u.isActive');				
        $query = $query->where('u.id= :emailid')->setParameter('emailid', $emailid);
	$query = $query->getQuery();
	$Result = $query->getResult();	        
        return $Result;                   
    }
    
}
?>