<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class UserBookingRepository extends EntityRepository
{
   
    /**
     * getUsersEventBooking - This function returns the number of seat reserved for 
     * any particular date and time for an event.
     * @param integer $eventId
     * @param integer $eventSeat
     * @param string $eventDate
     * @param string $eventTime
     * @return mixed
     * @author: Manu
     */
    public function getUsersEventBooking($eventId,$seatId,$eventDate,$eventTime){
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\UserBooking', 'b');
        $query = $query->select("count('so.id as numcount')" );
        $query = $query->join('b.seatOrder','so');        
        $query = $query->where("b.status = 1");
        $query = $query->andWhere("b.eventDate = :eventDate")->setParameter('eventDate',$eventDate);
        $query = $query->andWhere("b.eventTime = :eventTime")->setParameter('eventTime',$eventTime);
        $query = $query->andWhere("b.event = :eventId")->setParameter('eventId',$eventId);
        $query = $query->andWhere("so.eventSeat = :seatId")->setParameter('seatId',$seatId);
        $query = $query->groupBy("so.eventSeat");
        $query = $query->getQuery();
        $result = $query->getResult();
        
        return $result;
    }
    
    /**
     * getTotalOfEventBooking - This function returns the number of seat reserved for 
     * any particular date and time for an event. Also it fetches the count even if the payment is failed
     * @param integer $eventId
     * @param integer $eventSeat
     * @param string $eventDate
     * @param string $eventTime
     * @return mixed
     * @author: Manu
     */
    public function getTotalOfEventBooking($eventId,$seatId,$eventDate,$eventTime){
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query = $query->from('Admin\Entity\UserBooking', 'b');
        $query = $query->select("count('so.id as numcount')" );
        $query = $query->join('b.seatOrder','so');        
        //$query = $query->where("b.status = 1");
        $query = $query->andWhere("b.eventDate = :eventDate")->setParameter('eventDate',$eventDate);
        $query = $query->andWhere("b.eventTime = :eventTime")->setParameter('eventTime',$eventTime);
        $query = $query->andWhere("b.event = :eventId")->setParameter('eventId',$eventId);
        $query = $query->andWhere("so.eventSeat = :seatId")->setParameter('seatId',$seatId);
        $query = $query->groupBy("so.eventSeat");
        $query = $query->getQuery();
        $result = $query->getResult();
        
        return $result;
    }

    public function getUsersOrderHistory($userid, $sqlArr, $basepath=""){                
        $columnArr = ['ub.bookingOrderNo','e.eventName'];
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
        $query = $query->from('Admin\Entity\UserBooking', 'ub');
        $query = $query->select('u.id,ub.id as userbookingId,ub.eventDate as eventdate,ub.eventTime,ub.bookingOrderNo,e.eventName as eventname,e.eventVenueTitle,ub.bookingSeatCount');
        $query = $query->leftjoin('Admin\Entity\Users', 'u', 'WITH', 'u.id = ub.user');        
        $query = $query->leftjoin('Admin\Entity\Event', 'e', 'WITH', 'ub.event = e.id');        
        $query = $query->where("ub.user = ".$userid);
        $query = $query->andWhere("u.status != 2");
        
        $searchStr='';
        if(trim($searhKey) !=''){ 
            $searhKey = str_replace("<br>",'',trim($searhKey));               
            $query = $query->andwhere('e.eventName LIKE :eventname or e.eventVenueTitle LIKE :venue or ub.bookingOrderNo LIKE :booking')				
                           ->setParameter('eventname', '%'.$searhKey.'%')
                           ->setParameter('venue', '%'.$searhKey.'%')                     
                           ->setParameter('booking', '%'.$searhKey.'%');            
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
        //$query = $query->from('Admin\Entity\Users', 'u');
        $searchStr='';
        $query = $query->from('Admin\Entity\UserBooking', 'ub');
        $query = $query->select('u.id,ub.id as userbookingId,ub.eventDate as eventdate,ub.eventTime,ub.bookingOrderNo,e.eventName as eventname,e.eventVenueTitle,ub.bookingSeatCount');
        $query = $query->leftjoin('Admin\Entity\Users', 'u', 'WITH', 'u.id = ub.user');        
        $query = $query->leftjoin('Admin\Entity\Event', 'e', 'WITH', 'ub.event = e.id');        
        $query = $query->where("ub.user = ".$userid);
        $query = $query->andWhere("u.status != 2");
        $searchStr='';
        if(trim($searhKey) !=''){ 
            $searhKey = str_replace("<br>",'',trim($searhKey));               
            $query = $query->andWhere('e.eventName LIKE :eventname or e.eventVenueTitle LIKE :venue or ub.bookingOrderNo LIKE :booking')				
                           ->setParameter('eventname', '%'.$searhKey.'%')
                           ->setParameter('venue', '%'.$searhKey.'%')                     
                           ->setParameter('booking', '%'.$searhKey.'%');            
                           
        }        
        $query = $query->setMaxResults($limit);
        $query = $query->setFirstResult($offSet);
        $query = $query->orderBy($sortByColumnName, $sortType);		
        $query = $query->getQuery();
        $Result = $query->getResult();
        $key=0;
        $UserDataList['sEcho']  =   $sEcho;
        $UserDataList['iTotalRecords']         =    $totalRecordCount;
        $UserDataList['iTotalDisplayRecords']  =    $totalRecordCount;
        $title = '';
        $imageShow = '';
        $width = '';
        $height = '';                 
            foreach ($Result  as $userRow) {
                if(!isset($userRow['eventdate']) || ($userRow['eventdate'] == '') ){
                    $eventdate = "";
                }else{
                    $eventdate = $userRow['eventdate']->format('M d, Y');
                }
                if(!isset($userRow['eventTime']) || ($userRow['eventTime'] == '') ){
                    $eventtime = "";
                }else{
                    $eventtime = $userRow['eventTime']->format('h:i A');
                }
               $id = $userRow['id'];                     
               $UserDataList['data'][$key][0]  =  $userRow['bookingOrderNo']; 
               $UserDataList['data'][$key][1]  =  $userRow['eventname'];
               $UserDataList['data'][$key][2]  =  $userRow['eventVenueTitle'];
               $UserDataList['data'][$key][3]  =  $eventdate." ".$eventtime;
               $UserDataList['data'][$key][4]  =  $userRow['bookingSeatCount'];
               $UserDataList['data'][$key][5]  =  '<a href="'.$basepath.'/admin/order/detail/'.$userRow['userbookingId'].'">View</a>';                
               $key++;	
           
        }
        if($key == 0){ $UserDataList['aaData'] =''; }
        return $UserDataList;
    }     

}
