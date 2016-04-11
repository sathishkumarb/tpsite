<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class UsersRepository extends EntityRepository {

    /**
     * Function to check admin Login
     * @params mixed $data 
     * @return mixed $result
     * @author Manu Garg
     */
    public function verifyAdmin($data = array()) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id,u.email,u.password,u.username,u.userRole,u.isForgotStatus');
        $query = $query->where('u.email = :adminemail')->setParameter('adminemail', $data['email']);
        $query = $query->andWhere('u.password = :password')->setParameter('password', md5($data['password']));
        $query = $query->andWhere('u.status = 1');
        $query = $query->andWhere("u.userType != 'N'");
        $query = $query->getQuery();
//        print_r(array(
//            'sql' => $query->getSql(),
//            'parameters' => $query->getParameters(),
//        ));
//        die();
        $result = $query->getResult();
        return $result;
    }

    /**
     * Function to check admin password
     * @param string $password 
     * @return mixed $result
     * @author Manu Garg
     */
    public function checkAdminPassword($password) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id');
        $query = $query->where('u.password = :password')->setParameter('password', md5($password));
        $query = $query->andWhere('u.status = 1');
        $query = $query->andWhere("u.userType = 'A'");
        $query = $query->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * checkIfEmailAlreadyExist - Check if email exist in DB or not, 
     * Need to pass notInUserId param if need to edit entry for particular user
     * @param string $email
     * @param integer $notInUserId
     * @return mixed
     * @author Manu Garg
     */
    public function checkIfEmailAlreadyExist($email, $notInUserId = 0) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id');
        $query = $query->where('u.email = :email')->setParameter('email', $email);
        $query = $query->andWhere('u.status != 2');
        if (!empty($notInUserId)) {
            $query = $query->andWhere("u.id != :userId")->setParameter("userId", $notInUserId);
        }
        $query = $query->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * getUsersListingAtAdminForDataTable
     * @param mixed $sqlArr - Data received from datatable
     * @return mixed Data to be sent to Datatable in desired format
     * @author Manu Garg
     */
    public function getUsersListingAtAdminForDataTable($sqlArr, $userChangeStatusUrl, $userDeleteUrl, $basePath) {

        $sEcho = $sqlArr['sEcho'];

        /* To fetch Total no of records */
        $Result = $this->getUsersListingForDT($sqlArr, 0);
        $totalRecordCount = count($Result);

        /* To fetch records with paging */
        $Result = $this->getUsersListingForDT($sqlArr, $limited = 1);
        $key = 0;
        $UserDataList['sEcho'] = $sEcho;
        $UserDataList['iTotalRecords'] = $totalRecordCount;
        $UserDataList['iTotalDisplayRecords'] = $totalRecordCount;
        $em = $this->getEntityManager();
        foreach ($Result as $userRow) {
            $roleId = $userRow['roleId'];
            $planObj = $em->getRepository('\Admin\Entity\UserRole')->find($roleId);
            $id = $userRow['id'];
            if ($userRow['status'] == 1) {
                $status = '<button title="Click here to Deactivate this user" id="status_' . $id . '" onclick="activeInactiveUser(' . $id . ',\'inactive\');" class="btn btn-xs btn-danger" type="button">Deactivate User</button>';
            } else {
                $status = '<button title="Click here to activate this user" id="status_' . $id . '" onclick="activeInactiveUser(' . $id . ',\'active\');" class="btn btn-xs btn-success" type="button">Activate User</button>';
            }
            $deleteUrl1 = $userDeleteUrl . $id;
            $edit_url = $basePath . "/admin/user/edit/" . $id;
            $order_url = $basePath . "/admin/orderhistory/" . $id;
            $editstr = '<a id="edit" title="Click here to Edit this User" alt="Edit User" href="' . $edit_url . '" > <i class="icon-pencil"></i></a>';
            $deleteUrl = '<a id="del_' . $id . '" alt="Delete User" title="Click here to Delete this User" href="javascript:void(0);" onClick="deleteUser(' . $id . ')"> <i class="icon-trash"></i> </a>';
            $orderURL = '<button title="Click here to see order details of this user" id="order_' . $id . '" onclick="document.location.href=\'' . $order_url . '\';" class="btn btn-xs btn-default" type="button">Order</button>';

            $UserDataList['data'][$key][0] = $userRow['firstName'];
            $UserDataList['data'][$key][1] = $userRow['lastName'];
            $UserDataList['data'][$key][2] = $userRow['email'];
            $UserDataList['data'][$key][3] = $userRow['phone'];
            $UserDataList['data'][$key][4] = $planObj->getRoleName();
            $UserDataList['data'][$key][5] = $status . "&nbsp;&nbsp;" . $orderURL . "&nbsp;&nbsp;" . $editstr . "&nbsp;&nbsp;" . $deleteUrl;
            //$UserDataList['data'][$key][4]  =  $planObj->getRoleName();
            //$UserDataList['data'][$key][5]  =  $status."&nbsp;&nbsp;".$orderURL."&nbsp;&nbsp;".$editstr."&nbsp;&nbsp;".$deleteUrl;
            $key++;
        }
        if ($key == 0) {
            $UserDataList['data'] = '';
        }

        return $UserDataList;
    }

    /**
     * getUsersListingAtAdminForDataTable
     * @param mixed $sqlArr - Data received from datatable
     * @return mixed Data to be sent to Datatable in desired format
     * @author Manu Garg
     */
    public function getSecretKeyListingAtAdminForDataTable($sqlArr, $userChangeStatusUrl, $userDeleteUrl, $basePath) {
        $sEcho = $sqlArr['sEcho'];

        /* To fetch Total no of records */
        $Result = $this->getUsersListingForDT($sqlArr, 0);
        $totalRecordCount = count($Result);

        /* To fetch records with paging */
        $Result = $this->getUsersListingForDT($sqlArr, $limited = 1);
        $key = 0;
        $UserDataList['sEcho'] = $sEcho;
        $UserDataList['iTotalRecords'] = $totalRecordCount;
        $UserDataList['iTotalDisplayRecords'] = $totalRecordCount;

        foreach ($Result as $userRow) {
            $id = $userRow['id'];
            if (!empty($userRow['secretKey'])) {
                $status = '<button title="Click here to Re-Generate Secret Key of this user " id="status_' . $id . '" onclick="generateSecretKey(' . $id . ',\'inactive\');" class="btn btn-xs btn-success" type="button">Re-Generate Secret Key</button>';
            } else {
                $status = '<button title="Click here to Generate Secret Key of this user" id="status_' . $id . '" onclick="generateSecretKey(' . $id . ',\'active\');" class="btn btn-xs btn-success" type="button">Generate Secret Key</button>';
            }
            $deleteUrl1 = $userDeleteUrl . $id;
            $edit_url = $basePath . "/admin/user/edit/" . $id;
            $order_url = $basePath . "/admin/orderhistory/" . $id;
            $editstr = '<a id="edit" title="Click here to Edit this User" alt="Edit User" href="' . $edit_url . '" > <i class="icon-pencil"></i></a>';
            $deleteUrl = '<a id="del_' . $id . '" alt="Delete User" title="Click here to Delete this User" href="javascript:void(0);" onClick="deleteUser(' . $id . ')"> <i class="icon-trash"></i> </a>';
            $orderURL = '<button title="Click here to see order details of this user" id="order_' . $id . '" onclick="document.location.href=\'' . $order_url . '\';" class="btn btn-xs btn-default" type="button">Order</button>';

            $UserDataList['data'][$key][0] = $userRow['firstName'];
            $UserDataList['data'][$key][1] = $userRow['lastName'];
            $UserDataList['data'][$key][2] = $userRow['email'];
            $UserDataList['data'][$key][3] = $userRow['secretKey'];
            $UserDataList['data'][$key][4] = $status;
            $key++;
        }
        if ($key == 0) {
            $UserDataList['data'] = '';
        }
        return $UserDataList;
    }

    /**
     * getUsersListingForDT
     * @param mixed $sqlArr - Parameters sent from Data table 
     * @param integer $limited - Parameter used for paging in datatable
     * @return Mixed Array of user objects
     * @author Manu Garg
     */
    public function getUsersListingForDT($sqlArr, $limited = 0) {
        if ($limited == 1) {
            /* For Paging in DataTables */
            //$columnArr = ['u.firstName','u.lastName','u.email','u.phone','roleId'];
            $columnArr = ['u.firstName', 'u.lastName', 'u.email', 'u.phone'];
            $sortByColumnName = $columnArr[$sqlArr['sortcolumn']];
            $sortType = $sqlArr['sorttype'];
            $offSet = $sqlArr['iDisplayStart'];
            $limit = $sqlArr['limit'];
            $offSet = (int) $offSet;
            $limit = (int) $limit;
            $sortType = strtoupper($sortType);
            if ($sortType == "ASC") {
                $sortType = 'ASC';
            } else {
                $sortType = 'DESC';
            }
        }
        $searchKey = $sqlArr['searchKey'];
        //$roleKey         =    $sqlArr['currRole'];
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        /* $query  = $query->select('u.id,u.firstName,u.lastName,u.email,u.phone,u.status,IDENTITY(u.userRole) as roleId,u.secretKey');
          $query->Where("IDENTITY(u.userRole) != 1"); */
        $query = $query->select('u.id,u.firstName,u.lastName,u.email,u.phone,u.status,IDENTITY(u.userRole) as roleId');
        $query = $query->where("u.userType = 'N'");
        $query = $query->andWhere("u.status != 2");
        if (trim($searchKey) != '') {
            $searchKey = str_replace("<br>", '', trim($searchKey));
            $query = $query->andWhere('u.firstName LIKE :searchterm or u.lastName like :searchterm or u.email like :searchterm or u.phone like :searchterm')->setParameter('searchterm', '%' . $searchKey . '%');
        }
        /* if(trim($roleKey) !='')
          {
          $query = $query->andWhere("IDENTITY(u.userRole) = ".$roleKey);
          }else{
          $query = $query->andWhere("IDENTITY(u.userRole) = 2");
          } */
        // echo "<pre>";print_r($query);die("here");
        if ($limited == 1) {
            $query = $query->setMaxResults($limit);
            $query = $query->setFirstResult($offSet);
            $query = $query->orderBy($sortByColumnName, $sortType);
        }
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    public function getUserByEmail($email, $user_type) {
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
    public function verifyUser($data = array()) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id, u.email, u.password,u.username,u.isForgotStatus,u.firstName,u.lastName');
        $query = $query->where('u.email = :useremail')->setParameter('useremail', $data['email']);
        $query = $query->andWhere('u.password = :password')->setParameter('password', md5($data['password']));
        $query = $query->andWhere('u.status = 1');
        $query = $query->andWhere("u.userType != 'A'");
        $query = $query->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function getUserById($id) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Users', 'u');
        $query = $query->select('u.id,u.username,u.isForgotStatus, ur.roleName');
        $query = $query->join('u.userRoleData', 'ur');
        $query = $query->where('u.id = :id')->setParameter('id', $id);
        $query = $query->andWhere('u.status != 3');
        $query = $query->getQuery();
        $result = $query->getSingleResult();
        return $result;
    }


}
