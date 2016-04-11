<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class RolesRepository extends EntityRepository
{

    
    /**
     * checkIfRoleAlreadyExist - Check if roleName exist in DB or not, 
     * Need to pass notInUserId param if need to edit entry for particular user
     * @param string $roleName
     * @param integer $notInUserId
     * @return mixed
     * @author Mohit Chauhan
     */
    public function checkIfRoleAlreadyExist($roleName,$notInUserId=0){
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('r');
        $query = $query->from('Admin\Entity\UserRole', 'r');
        $query = $query->select('r.id');		
        $query = $query->where('r.roleName = :roleName')->setParameter('roleName',$roleName);
        if(!empty($notInUserId)){
            $query = $query->andWhere("r.id != :userId")->setParameter("userId", $notInUserId);
        }
        $query = $query->getQuery();
        $result = $query->getResult();
        return $result;
    }
    
    /**
     * getRolesListingAtAdminForDataTable
     * @param mixed $sqlArr - Data received from datatable
     * @return mixed Data to be sent to Datatable in desired format
     * @author Manu Garg
     */
    public function getRolesListingAtAdminForDataTable($sqlArr, $userChangeStatusUrl, $userDeleteUrl, $basePath){
        $sEcho             =    $sqlArr['sEcho'];
        
        /* To fetch Total no of records */
        $Result = $this->getRolesListingForDT($sqlArr,0);
        $totalRecordCount =  count($Result);
        
        /* To fetch records with paging */
        $Result = $this->getRolesListingForDT($sqlArr, $limited=1);
        $key=0;
        $UserDataList['sEcho']  =   $sEcho;
        $UserDataList['iTotalRecords']         =    $totalRecordCount;
        $UserDataList['iTotalDisplayRecords']  =    $totalRecordCount;
                
        foreach ($Result  as $userRow) {                                      
            $id = $userRow['id'];
            /*if($userRow['status']==1){
               $status = '<button title="Click here to Deactivate this user" id="status_'.$id.'" onclick="activeInactiveUser('.$id.',\'inactive\');" class="btn btn-xs btn-danger" type="button">Deactivate User</button>';
                
            }else{
                $status = '<button title="Click here to activate this user" id="status_'.$id.'" onclick="activeInactiveUser('.$id.',\'active\');" class="btn btn-xs btn-success" type="button">Activate User</button>';
            }*/
            $deleteUrl1 = $userDeleteUrl.$id;
            $edit_url = $basePath."/admin/capability/editrole/".$id;
            $editstr = '<a id="edit" title="Click here to Edit this User" alt="Edit User" href="'.$edit_url.'" > <i class="icon-pencil"></i></a>';
            $deleteUrl = '<a id="del_'.$id.'" alt="Delete User" title="Click here to Delete this User" href="javascript:void(0);" onClick="deleteRole('.$id.')"> <i class="icon-trash"></i> </a>';
            
            $UserDataList['data'][$key][0]  =  $userRow['roleName'];
            $UserDataList['data'][$key][1]  =  $editstr."&nbsp;&nbsp;".$deleteUrl;
            $key++;	
        }
        if($key == 0){ $UserDataList['data'] =''; }
        
        return $UserDataList;
    }
    
    /**
     * getRolesListingForDT
     * @param mixed $sqlArr - Parameters sent from Data table 
     * @param integer $limited - Parameter used for paging in datatable
     * @return Mixed Array of user objects
     * @author Manu Garg
     */
    public function getRolesListingForDT($sqlArr,$limited=0){    
        if($limited==1){
            /* For Paging in DataTables */
            $columnArr = ['r.roleName'];
            $sortByColumnName  =    $columnArr[$sqlArr['sortcolumn']];
                    
            $sortType          =    $sqlArr['sorttype'];
            $offSet            =    $sqlArr['iDisplayStart'];
            $limit             =    $sqlArr['limit'];       
		
            $offSet = (int)$offSet;
            $limit =(int) $limit;
            
            $sortType = strtoupper($sortType);
            if($sortType == "ASC"){
                $sortType ='ASC';
            } else {  
                $sortType ='DESC'; 
            }
        }
        
        $searchKey         =    $sqlArr['searchKey'];
        
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('r');
        $query = $query->from('Admin\Entity\UserRole', 'r');
        $query  = $query->select('r.id,r.roleName');
		$query = $query->where("r.id not in('1','2') ");
		$query = $query->andWhere("r.status = 1");
        if(trim($searchKey) !=''){ 
            $searchKey = str_replace("<br>",'',trim($searchKey));               
            $query = $query->andWhere('r.roleName LIKE :searchterm')				
                           ->setParameter('searchterm', '%'.$searchKey.'%');
        }
        if($limited==1){
            $query = $query->setMaxResults($limit);
            $query = $query->setFirstResult($offSet);
            $query = $query->orderBy($sortByColumnName, $sortType);	
        }
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }
    
    public function getUserByEmail($email , $user_type){
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id,u.username,u.isForgotStatus');		
        $query = $query->where('u.email = :email')->setParameter('email', $email);
        $query = $query->andWhere('u.userType = :usertype')->setParameter('usertype', $user_type);
        $query = $query->andWhere('u.status != 3');        
        $query = $query->getQuery();
        $result = $query->getResult();        
        return $result;     
    }
    /**
    * Function to check user Login
    * @params mixed $data 
    * @return mixed $result
    * @author Aditya
    */
    public function verifyUser( $data = array() )
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id, u.email, u.password,u.username,u.isForgotStatus,u.firstName,u.lastName');		
        $query = $query->where('u.email = :useremail')->setParameter('useremail', $data['email']);
        $query = $query->andWhere('u.password = :password')->setParameter('password', md5($data['password']));
        $query = $query->andWhere('u.status = 1');
        $query = $query->andWhere("u.userType = 'N'");
        $query = $query->getQuery();
        $result = $query->getResult();
        return $result;     
    }
}
