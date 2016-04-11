<?php
namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class CmsRepository extends EntityRepository{            
    
    
    /**
     * getCmsById - This function is used to fetch category by Id
     * @param type $cmsid
     * @return mixed
     */
    public function getCmsById($cmsid){           
        $em = $this->getEntityManager();
	$query = $em->createQueryBuilder('u');
	$query = $query->from('Admin\Entity\Cms', 'u');
	$query = $query->select('u.pageTitle, u.content, u.keywords, u.metaTag, u.metaDesc');				
        $query = $query->where('u.id= :cmsid')->setParameter('cmsid', $cmsid);
	$query = $query->getQuery();
	$Result = $query->getResult();	        
        return $Result;               
    }
    
    public function getCmsListing($sqlArr, $editUrl, $basePath){
        $columnArr = ['u.pageTitle'];
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
        $query = $query->from('Admin\Entity\Cms', 'u');
        $query  = $query->select('u.id, u.pageTitle AS cms');						
        $searchStr='';
        if(trim($searhKey) !=''){ 
            $searhKey = str_replace("<br>",'',trim($searhKey));               
            $query = $query->where('u.pageTitle LIKE :cmsname')				
                           ->setParameter('cmsname', '%'.$searhKey.'%');
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
        
        $query = $query->from('Admin\Entity\Cms', 'u');
        $searchStr='';
        
        $query  = $query->select('u.id, u.pageTitle AS cms');      
        $searchStr='';
        if(trim($searhKey) !=''){ 
            $searhKey = str_replace("<br>",'',trim($searhKey));               
            $query = $query->where('u.pageTitle LIKE :cmsname')				
                            ->setParameter('cmsname', '%'.$searhKey.'%');
        }        
        $query = $query->setMaxResults($limit);
        $query = $query->setFirstResult($offSet);
        $query = $query->orderBy($sortByColumnName, $sortType);		
        $query = $query->getQuery();
        $Result = $query->getResult();
        $key=0;
        $CmsDataList['sEcho']  =   $sEcho;
        $CmsDataList['iTotalRecords']         =    $totalRecordCount;
        $CmsDataList['iTotalDisplayRecords']  =    $totalRecordCount;
        $title = '';
        $imageShow = '';
        $width = '';
        $height = '';        
        foreach ($Result  as $cmsRow) {                        
            $cmsid = $id = $cmsRow['id'];            
            $CmsDataList['aaData'][$key][0]  =  $cmsRow['cms'];                
            $edit_url = $basePath."/admin/cms/cmsedit/".$cmsid;
            $CmsDataList['aaData'][$key][1]  =   "<a href= '".$edit_url."' title='Edit Cms'><i class='icon-edit'></i></a>&nbsp;";
            $key++;	
        }
        if($key == 0){ $CmsDataList['aaData'] =''; }
        return $CmsDataList;
    }
}   
?>
