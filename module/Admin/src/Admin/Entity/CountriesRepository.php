<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class CountriesRepository extends EntityRepository{    
    /**
     * Function to get All country list 
     * @return type
     * @author Aditya
     */
    public function getCountryList(){
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\Countries', 'u');
        $query = $query->select('u.id, u.countryName');
        $query = $query->where('u.countryExist = 1');
        $query = $query->getQuery();
        $result = $query->getResult();
        foreach($result as $key=>$val){
            
        }
        return $result;     
    }   
}