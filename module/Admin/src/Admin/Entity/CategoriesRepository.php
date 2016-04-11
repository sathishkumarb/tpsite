<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class CategoriesRepository extends EntityRepository {

    /**
     * getCategory - Used to get particular category
     * @param string $catname 
     * @return mixed
     */
    public function getCategory($catname) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Categories', 'u');
        $query = $query->select('u.categoryName');
        $query = $query->where('u.categoryName= :category_name')->setParameter('category_name', $catname);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    /**
     * getCategoryById - This function is used to fetch category by Id
     * @param type $catid
     * @return mixed
     */
    public function getCategoryById($catid) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Categories', 'u');
        $query = $query->select('u.categoryName, u.icon');
        $query = $query->where('u.id= :catid')->setParameter('catid', $catid);
        $query = $query->getQuery();
        $Result = $query->getResult();
        return $Result;
    }

    /**
     * getCategoryListing - DIsplays all category on category listing page
     * @param Array $sqlArr
     * @param string $editUrl
     * @return Array
     * @author Aditya
     */
    public function getCategoryListing($sqlArr, $editUrl, $basePath) {
        $columnArr = ['u.icon', 'u.categoryName'];
        $sortByColumnName = $columnArr[$sqlArr['sortcolumn']];
        $searhKey = $sqlArr['searchKey'];
        $sortType = $sqlArr['sorttype'];
        $offSet = $sqlArr['iDisplayStart'];
        $sEcho = $sqlArr['sEcho'];
        $limit = $sqlArr['limit'];

        $offSet = (int) $offSet;
        $limit = (int) $limit;

        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Categories', 'u');
        $query = $query->select('u.id, u.categoryName, u.icon AS cat');
        $query = $query->where('u.status = :status')->setParameter('status', 1);
        $searchStr = '';
        if (trim($searhKey) != '') {
            $searhKey = str_replace("<br>", '', trim($searhKey));
            $query = $query->andwhere('u.categoryName LIKE :catname')
                    ->setParameter('catname', '%' . $searhKey . '%');
        }
        $query = $query->getQuery();
        $Result = $query->getResult();
        $totalRecordCount = count($Result);
        $recArr[] = array();

        $query = '';
        $query = $em->createQueryBuilder('u');
        $sortType = strtoupper($sortType);
        if ($sortType == "ASC") {
            $sortType = 'ASC';
        } else {
            $sortType = 'DESC';
        }

        $query = $query->from('Admin\Entity\Categories', 'u');
        $searchStr = '';

        $query = $query->select('u.id, u.icon, u.categoryName AS cat');
        $query = $query->where('u.status = :status')->setParameter('status', 1);
        // $query = $query->innerjoin('Restaurant\Entity\Restaurants', 'r', 'WITH', 'u.id = r.user');
        //$query = $query->leftjoin('Restaurant\Entity\Languages', 'l', 'WITH', 'u.language = l.id');
        $searchStr = '';
        if (trim($searhKey) != '') {
            $searhKey = str_replace("<br>", '', trim($searhKey));
            $query = $query->andWhere('u.categoryName LIKE :catname')
                    ->setParameter('catname', '%' . $searhKey . '%');
        }
        $query = $query->setMaxResults($limit);
        $query = $query->setFirstResult($offSet);
        $query = $query->orderBy($sortByColumnName, $sortType);
        $query = $query->getQuery();
        $Result = $query->getResult();
        $key = 0;
        $UserDataList['sEcho'] = $sEcho;
        $UserDataList['iTotalRecords'] = $totalRecordCount;
        $UserDataList['iTotalDisplayRecords'] = $totalRecordCount;
        $title = '';
        $imageShow = '';
        $width = '';
        $height = '';
        foreach ($Result as $catRow) {
            $catid = $id = $catRow['id'];
            $icon_url = $basePath . "/uploads/category/" . $catRow['icon'];
            $UserDataList['aaData'][$key][0] = "<span class='cat_icon'><img src='" . $icon_url . "' width='50' height='50' /></span>";
            $UserDataList['aaData'][$key][1] = $catRow['cat'];
            $del_string = $catid . ',"' . $catRow['cat'] . '"';
            $edit_url = $basePath . "/admin/category/categoryedit/" . $catid;
            $UserDataList['aaData'][$key][2] = "<a href= '" . $edit_url . "' title='Edit Category' onclick='javascript: return confirmeditcategory(" . $catid . ");'><i class='icon-edit'></i></a>&nbsp;&nbsp;&nbsp;<a href= 'javascript:void(0)' id=" . $catid . " title='Delete Category' onclick='javascript: return delcategory(" . $del_string . ");'><i class='icon-trash'></i> </a>";
            $key++;
        }
        if ($key == 0) {
            $UserDataList['aaData'] = '';
        }
        return $UserDataList;
    }

}

?>