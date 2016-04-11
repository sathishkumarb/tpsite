<?php

/**
 *  Common View Helper To be Used in  View Files (.phtml)
 * 
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Application\Form as Forms;
use Zend\Mail as Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Commonviewhelper extends AbstractHelper implements ServiceLocatorAwareInterface {

    protected $em;
    protected $authservice;
    protected $serviceLocator;

    public function setEntityManager(EntityManager $em) {
        $this->em = $em;
    }

    public function getEntityManager() {
        if (null === $this->em) {
            // first one gives access to other view helpers  
            $helperPluginManager = $this->getServiceLocator();
            // the second one gives access to... other things.  
            $serviceManager = $helperPluginManager->getServiceLocator();
            $this->em = $serviceManager->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    /**
     * Set the service locator. 
     * 
     * @param ServiceLocatorInterface $serviceLocator 
     * @return CustomHelper 
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get the service locator. 
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface 
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function getAuthService() {
        if (!$this->authservice) {
            // first one gives access to other view helpers  
            $helperPluginManager = $this->getServiceLocator();
            // the second one gives access to... other things.  
            $serviceManager = $helperPluginManager->getServiceLocator();
            $this->authservice = $serviceManager->get('AuthService');
        }
        return $this->authservice;
    }

    /**
     * compare - Compare two arrays containing dates
     * @param mixed $a
     * @param mixed $b
     * @return int
     * @author Manu Garg
     */
    public function compare($a, $b) {
        if ($a['date'] == $b['date'])
            return 0;
        return (strtotime($a['date']) > strtotime($b['date'])) ? 1 : -1;
    }

    /**
     * getDateTimeForEventsListing
     * @param \Admin\Entity\Event $event
     * @return string 
     * @author Manu Garg
     */
    public function getDateTimeForEventsListing(\Admin\Entity\Event $event) {
        $dateArr = array(); /* Array for putting date */
        $i = 0;
        $computedDate = ""; /* String to return */
        $eventSchedules = $event->getEventSchedule(); /* Fetching Event Schedules */
        if (!empty($eventSchedules)) {
            foreach ($event->getEventSchedule() as $schedule) {
                /* Event Schedules Loop */
                if ($i != 0) {
                    $j = $schedule->getEventDate()->format('Y-m-d');

                    /* Checking Date if its already in the Array */
                    $checkVal = $this->checkValueInArray($dateArr, "date", $j);

                    if ($checkVal == -1) {
                        /* If value not found in an Array then insert it into array */
                        $dateArr[$i]['date'] = $j;
                        $dateArr[$i]['time'] = $schedule->getEventTime()->format("h:i A");
                        $i++;
                    } else {
                        /* If value found in an Array then update with key */
                        $dateArr[$checkVal]['time'] .= "&nbsp;•&nbsp;" . $schedule->getEventTime()->format("h:i A");
                    }
                }if ($i == 0) {
                    /* Directly Puting first Schedule into an array */
                    $dateArr[$i]['date'] = $schedule->getEventDate()->format('Y-m-d');
                    $dateArr[$i]['time'] = $schedule->getEventTime()->format("h:i A");
                    ;
                    $i++;
                }
            }

            $lenArr = count($dateArr); /* checking length of an Array */

            if ($lenArr > 1) {

                /* if array length greater then 1 then sort array based on date */
                /* For showing date Range */
                $data = usort($dateArr, array($this, "compare"));

                $date1 = date_create_from_format('Y-m-d', $dateArr[0]['date']);
                $date2 = date_create_from_format('Y-m-d', $dateArr[$lenArr - 1]['date']);
                $computedDate = $date1->format('F d') . " - " . $date2->format('F d, Y');
            } else {
                /* For showing date and time */
                $date1 = date_create_from_format('Y-m-d', $dateArr[0]['date']);
                $computedDate = $date1->format('F d, Y') . "&nbsp;•&nbsp;" . $dateArr[0]['time'];
            }
        }
        return $computedDate;
    }

    /**
     * checkValueInArray
     * @param array $arr - 2-Dimensional Array 
     * @param string $field -Field of which value is checked
     * @param string $value
     * @return integer
     */
    public function checkValueInArray($arr, $field, $value) {
        if (!empty($arr)) {
            foreach ($arr as $key => $val) {
                if ($val[$field] === $value) {

                    return $key;
                }
            }
        }
        return -1;
    }

    /**
     * getUserSeatCount - This function returns the number of seat reserved for 
     * any particular date and time for an event.
     * @param integer $eventId
     * @param integer $eventSeat
     * @param string $eventDate
     * @param string $eventTime
     * @return int
     * @author: Manu
     */
    public function getUserSeatCount($eventId, $eventSeat, $eventDate, $eventTime) {

        $em = $this->getEntityManager(); /* Call Entity Manager */

        $userBookingObj = $em->getRepository('\Admin\Entity\UserBooking')
                ->getUsersEventBooking($eventId, $eventSeat, $eventDate, $eventTime);

        if (!empty($userBookingObj)) {

            return $userBookingObj[0][1];
        }
        return 0;
    }

}
