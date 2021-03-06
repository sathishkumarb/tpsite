<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Form\Annotation\AnnotationBuilder;
use Application\Form as Forms;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Zend\Mail as Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Zend\File\Transfer\Adapter\Http;


use Zend\Validator\File\Extension;
use Zend\Validator\File\IsImage;
use Zend\Validator\File\Size;
use Zend\View\Model\JsonModel;


class EventController extends AbstractActionController {

    protected $em;
    protected $authservice;

    public function onDispatch(MvcEvent $e) {
        /*
          $admin_session = new Container('admin');
          $username = $admin_session->username;
          if(empty($username)) {

          return $this->redirect()->toRoute('adminlogin');
          }
         */

        /* Set Default layout for all the actions */
        $this->layout('layout/layout');
        $em = $this->getEntityManager();
        $cities = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => 2));
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $signupForm = new Forms\SignupForm();
        $loginForm = new Forms\LoginForm();
        $forgotpassForm = new Forms\ForgotPasswordForm();

        $this->layout()->signupForm = $signupForm;
        $this->layout()->loginForm = $loginForm;
        $this->layout()->forgotpassForm = $forgotpassForm;
        $this->layout()->cities = $cities;
        $this->layout()->categories = $categories;
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $city = "";
        $searchSession = new Container("searchsess");
        $searchType = "";
        $searchTerm = "";
        if ($searchSession->offsetExists("type")) {
            $searchType = $searchSession->offsetGet("type");
            $searchTerm = $searchSession->offsetGet("searchTerm");
        }
        if ($searchType == "category" && $searchTerm != "") {
            $this->layout()->searchedCategory = $searchTerm;
        }
        if ($searchType == "city" && $searchTerm != "") {
            $this->layout()->userCity = $searchTerm;
        }
        if (!empty($userid)) {
            $msg = 'You are already logged in.';
            $status = 1;
            $this->layout()->setVariable('userId', $user_session->userId);
            $this->layout()->setVariable('username', $user_session->userName);
            $username = $user_session->userName;
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($user_session->userId);
            $city = $tmp_user->getCity();
            if ($searchType == "city" && $searchTerm != "") {
                $this->layout()->userCity = $searchTerm;
            } else {
                if (!empty($city)) {
                    $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                    $this->layout()->userCity = $cityObj->getCityName();
                }
            }
        } else {
            $this->layout()->setVariable('userId', '');
        }
        return parent::onDispatch($e);
    }

    public function setEntityManager(EntityManager $em) {
        $this->em = $em;
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()
                    ->get('AuthService');
        }
        return $this->authservice;
    }

    /**
     * Deleted image with from a given src.
     *
     * @method deleteimageAction
     *
     * @return bool
     */
    protected function deleteimageAction() {
        $request = $this->getRequest();
        $status = false;

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            if ($request->isXmlHttpRequest()) {
                if (is_file('public'.$data['img'])) {
                    unlink('public'.$data['img']);
                    $status = true;
                }
            }
        }

        return $status;
    }

    /**
     * Get all files from all folders and list them in the gallery
     * getcwd() is there to make the work with images path easier.
     *
     * @return JsonModel
     */
    protected function filesAction(){

        chdir(getcwd().'/public/');

        $dir = new \RecursiveDirectoryIterator('userfiles/', \FilesystemIterator::SKIP_DOTS);
        $it = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);
        $it->setMaxDepth(50);
        $files = array();
        $i = 0;
        foreach ($it as $file) {
            if ($file->isFile()) {
                $files[$i]['filelink'] = DIRECTORY_SEPARATOR.$file->getPath().DIRECTORY_SEPARATOR.$file->getFilename();
                $files[$i]['filename'] = $file->getFilename();
                $i++;
            }
        }
        chdir(dirname(getcwd()));
        $model = new JsonModel();
        $model->setVariables(array('files' => $files));

        return $model;
    }

    /**
     * Upload all images async.
     *
     * @return array
     */
    private function prepareImages() {
        $adapter = new Http();

        $size = new Size(array('min' => '10kB', 'max' => '5MB','useByteString' => true));
        $extension = new Extension(array('jpg','gif','png','jpeg','bmp','webp','svg'), true);

        if (extension_loaded('fileinfo')) {
            $adapter->setValidators([new IsImage()]);
        }

        $adapter->setValidators([$size, $extension]);

        $uri = $this->getRequest()->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $base = sprintf('%s://%s', $scheme, $host);

        $uploadsDir = getcwd() . '/public/uploads';
        if (!file_exists($uploadsDir)) {
            mkdir(($uploadsDir), 0777, true);
        }
        $uploadsDirPath = getcwd() . '/public/uploads/event/';
        if (!file_exists($uploadsDirPath)) {
            mkdir(($uploadsDirPath), 0777, true);
        }

        $adapter->setDestination( $uploadsDirPath);

        return $this->uploadFiles($adapter);
    }

    /**
     * @param Http $adapter
     *
     * @return array
     */
    private function uploadFiles(Http $adapter) {
        $uploadStatus = array();

        foreach ($adapter->getFileInfo() as $key => $file) {
            if (!$adapter->isValid($file['name'])) {
                foreach ($adapter->getMessages() as $key => $msg) {
                    $uploadStatus['errorFiles'][] = $file['name'].' '.strtolower($msg);
                }
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = md5(rand(). $file['name']) . '.' . $ext;
            $adapter->addFilter('Rename', array('target' => getcwd() . '/public/uploads/event/'.$newFileName,'overwrite' => true));
            // @codeCoverageIgnoreStart
            
            if (!$adapter->receive($file['name'])) {
                $uploadStatus['objname'][] = $file['name'];
                $uploadStatus['errorFiles'][] = $file['name'].' was not uploaded';
            } else {
                $uploadStatus['objname'][] = $newFileName;
                $uploadStatus['successFiles'][] = $file['name'].' was successfully uploaded';
            }
            // @codeCoverageIgnoreEnd
        }

        return $uploadStatus;
    }

    public function frontendeventimageuploadAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */

        $objCategories = $em->getRepository('Admin\Entity\Categories')->findBy(array('status' => 1));
        $request = $this->getRequest();  /* Fetching Request */
        $formdata = array();
          
        if ($request->isPost()) {
          
            $files =  $request->getFiles()->toArray();
            //$fileName = $files['picture']['name'];
            $request = $this->getRequest();
            $data = array();

            if ($request->isXmlHttpRequest()) {
                $filedata = $this->prepareImages();
            }
                                        
        }
        return new JsonModel(array(
            'FileUploadStatus' => json_encode($filedata),
        ));
    }

    public function frontendeventaddAction() {

        $em = $this->getEntityManager(); /* Call Entity Manager */

        $objCategories = $em->getRepository('Admin\Entity\Categories')->findBy(array('status' => 1));
        $objCities = $em->getRepository('\Admin\Entity\City')->findAll();
        $objCountries = $em->getRepository('Admin\Entity\Countries')->findBy(array('countryExist' => 1));
        // $cat = array();

        // foreach ($objCategories as $index =>$categories) {                                                        
           
        //    $cat[$index]['id'] = $categories->getCategoryName();
        //    $cat[$index]['name'] = $categories->getCategoryName();
           
        // }  

        return new ViewModel(array(
            //'dataCategories' => json_encode($cat),
            'dataCategories' => $objCategories,
            'dataCountries' => $objCountries,
            'dataCities' => $objCities,
        ));
    }

    public function frontendeventaddprocessAction(){
$request = $this->getRequest();  /* Fetching Request */

        
        $formdata = array();

        if ($request->isPost()) {

          
            $request = $this->getRequest();

            $this->layout()->pageTitle = 'Add Event'; /* Setting page title */
            $em = $this->getEntityManager(); /* Call Entity Manager */
            /* Check with passed password exist in DB */
            
            $objCountries = $em->getRepository('Admin\Entity\Countries')->findBy(array('countryExist' => 1));
            $data = $request->getPost();
            $eventname = $data['eventname'];
            $eventlink = $data['youtubelink'];
            $venuename = $data['venuename'];
            $eventlocation = $data['eventlocation'];
            $eventcategory = $data['eventcategory'];
            $eventcity = $data['eventcity'];
            $eventinfo = $data['eventinfo'];
            $eventpicture = $data['eventpicture'];
 
            try {
                echo "coming";
                $objCityId = $em->getRepository('Admin\Entity\City')->findOneBy(array('id' => $eventcity));
                $objCountryId = $em->getRepository('Admin\Entity\Countries')->findOneBy(array('id' => $objCityId->getCountryId()));
                $objCategoryId = $em->getRepository('Admin\Entity\Categories')->findOneBy(array('id' => $eventcategory));
                $objLayoutId = $em->getRepository('Admin\Entity\Layout')->findOneBy(array('id' => 1));
              
                $currentDate = date_create(date('Y-m-d H:i:s'));
                // Save Event details
                $eventObj = new Entities\Event();
                $eventObj->setEventName($eventname);
                $eventObj->setEventDesc($eventinfo);
                $eventObj->setEventCountry($objCountryId);
                $eventObj->setEventCity($objCityId);
                $eventObj->setEventVenueTitle($venuename);
                $eventObj->setEventImageBig($eventpicture);
                $eventObj->setEventLink($eventlink);
                $eventObj->setCategory($objCategoryId);
                $eventObj->setStatus(1);
                $eventObj->setLayout($objLayoutId);
                $eventObj->setCreatedDate($currentDate);
               
                $em->persist($eventObj);
                $em->flush();
                echo "last insert event id".$eventObj->getId();
                die();
               
            } catch (Zend_Exception $e) {

               
                 return new JsonModel(array(
                'eventId' => $eventObj->getId(),
                'message' => "Caught exception: " . get_class($e)." Message: Event Entity" . $e->getMessage(),

            ));
               
            }
           

        }

        //     $uploadsDir = getcwd() . '/public/uploads';
    }
    

    public function searchAction() {
        $this->layout()->pageTitle = "Search Events";
        $em = $this->getEntityManager();
        //$cities = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => 2));
        // added by Shehan@ITSthe1 - 28012016
        $cities = $em->getRepository('\Admin\Entity\City')->findBy(array('supported' => 1));
        // end change
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $searchType = $this->params('type');
        $searchKey = $this->params('searchval');
        $limit = 2;
        $offset = $this->params()->fromQuery('offset');
        if ($offset == "") {
            $offset = 0;
        }
        $isScroll = $this->params()->fromQuery('isscroll');
        $commonPlugin = $this->Common();
        //echo $offset . "==". $limit ; 
        $events = $commonPlugin->getSearchEvents($searchType, $searchKey, $offset, $limit);
        $allEvents = $commonPlugin->getSearchEvents($searchType, $searchKey);
        $eventsCount = count($allEvents);
        //echo "==".count($events)."==".$eventsCount;//die;
        $uniques = array();
        $unique_artist = array();
        $unique_venue = array();
        /** Fetch Unique Artist From events * */
        foreach ($allEvents as $event) {
            $uniques[$event['artist']] = $event;
        }
        foreach ($uniques as $unique) {
            $unique_artist[] = $unique['artist'];
        }
        unset($uniques);
        $uniques = array();
        /** Fetch Unique Venue from events * */
        foreach ($allEvents as $event) {
            $uniques[$event['venue']] = $event;
        }
        foreach ($uniques as $unique) {
            $unique_venue[] = $unique['venue'];
        }
        /* Checking User session and fetch its set saved city */
        $user_session = new Container('user');
        $userId = $user_session->userId;
        if (!empty($userId)) {
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($userId);
            $city = $tmp_user->getCity();
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $this->layout()->userCity = $cityObj->getCityName();
            } else {
                $this->layout()->userCity = "";
            }
        } else {
            $this->layout()->userCity = "";
        }
        /* Checking User session and fetch its set saved city */
        $error_msg = "";
        $searchSession = new Container("searchsess");
        $searchSession->offsetSet('type', $searchType);
        $searchSession->offsetSet('searchTerm', $searchKey);
        $this->layout()->searchedCategory = "";
        switch ($searchType) {
            case 'city':
                if ($searchKey == "") {
                    $error_msg = "No city selected";
                } else {
                    $city = $em->getRepository('\Admin\Entity\City')->find($searchKey);
                    $searchtitle = 'Search results for “' . $city->getCityName() . '"';
                    $this->layout()->userCity = $city->getCityName();
                    $searchSession->offsetSet('searchTerm', $city->getCityName());
                }
                break;
            case 'category':
                if ($searchKey == "") {
                    $error_msg = "No category selected";
                } else {
                    $category = $em->getRepository('\Admin\Entity\Categories')->find($searchKey);
                    $searchtitle = 'Search results for “' . $category->getCategoryName() . '"';
                    $this->layout()->searchedCategory = $category->getCategoryName();
                    $searchSession->offsetSet('searchTerm', $category->getCategoryName());
                }
                break;
            case 'title':
                if ($searchKey == "") {
                    $error_msg = "No event selected";
                } else {
                    $searchtitle = 'Search results for “' . $searchKey . '"';
                }
                break;
            case 'artist':
                if ($searchKey == "") {
                    $error_msg = "No artist selected";
                } else {
                    $searchtitle = 'Search results for “' . $searchKey . '"';
                }
                break;
            case 'venue':
                if ($searchKey == "") {
                    $error_msg = "No Venue Selected";
                } else {
                    $searchtitle = 'Search results for “' . $searchKey . '"';
                }
                break;
            case 'results':
                $searchtitle = "All results";
                break;
        }
        $this->layout()->cities = $cities;
        $this->layout()->categories = $categories;
        if ($isScroll == 1) {
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('events' => $events, 'eventsCount' => $eventsCount, 'searchType' => $searchType, 'searchKey' => $searchKey, 'isScroll' => $isScroll))->setTerminal(true);
            return $viewModel;
        } else {
            return array('events' => $events, 'search_title' => $searchtitle,
                'error' => $error_msg, 'artists' => $unique_artist,
                'venus' => $unique_venue, 'limit' => $limit,
                'eventsCount' => $eventsCount, 'searchType' => $searchType,
                'searchKey' => $searchKey, 'isScroll' => $isScroll);
        }
    }

    public function eventdayAction() {
        $this->layout()->pageTitle = "Search Events";
        $em = $this->getEntityManager();
        //$cities = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => 2));
        // added by Shehan@ITSthe1 - 28012016
        //$cities = $em->getRepository('\Admin\Entity\City')->findBy(array('supported' => 1));
        // end change
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $dateType = $this->params('type');
        $dateKey = $this->params('searchval');
        $limit = 4;
        $offset = $this->params()->fromQuery('offset');
        if ($offset == "") {
            $offset = 0;
        }
        $isScroll = $this->params()->fromQuery('isscroll');
        $commonPlugin = $this->Common();
        //echo $offset . "==". $limit ; 
        $events = $commonPlugin->getDateEvents($dateType, $dateKey, $offset, $limit);
        $allEvents = $commonPlugin->getDateEvents($dateType, $dateKey);
        $eventsCount = count($allEvents);
        //echo "==".count($events)."==".$eventsCount;//die;
        $uniques = array();
        $unique_artist = array();
        $unique_venue = array();
        /** Fetch Unique Artist From events * */
        foreach ($allEvents as $event) {
            $uniques[$event['artist']] = $event;
        }
        foreach ($uniques as $unique) {
            $unique_artist[] = $unique['artist'];
        }
        unset($uniques);
        $uniques = array();
        /** Fetch Unique Venue from events * */
        foreach ($allEvents as $event) {
            $uniques[$event['venue']] = $event;
        }
        foreach ($uniques as $unique) {
            $unique_venue[] = $unique['venue'];
        }
        /* Checking User session and fetch its set saved city */
        $user_session = new Container('user');
        $userId = $user_session->userId;
        if (!empty($userId)) {
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($userId);
            $city = $tmp_user->getCity();
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $this->layout()->userCity = $cityObj->getCityName();
            } else {
                $this->layout()->userCity = "";
            }
        } else {
            $this->layout()->userCity = "";
        }
        /* Checking User session and fetch its set saved city */
        $error_msg = "";
        $searchSession = new Container("searchsess");
        $searchSession->offsetSet('type', $dateType);
        $searchSession->offsetSet('searchTerm', $dateKey);
        $this->layout()->searchedCategory = "";
        
        //$this->layout()->cities = $cities;
        $this->layout()->categories = $categories;
        if ($isScroll == 1) {
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('events' => $events, 'eventsCount' => $eventsCount, 'dateType' => $dateType, 'dateKey' => $dateKey, 'isScroll' => $isScroll))->setTerminal(true);
            return $viewModel;
        } else {
            return array('events' => $events, 
                'error' => $error_msg, 'artists' => $unique_artist,
                'venus' => $unique_venue, 'limit' => $limit,
                'eventsCount' => $eventsCount, 'dateType' => $dateType,
                'dateKey' => $dateKey, 'isScroll' => $isScroll);
        }
    }

    /**
     * 
     * @return type
     */
    public function eventajaxsearchAction() {
        $this->layout()->pageTitle = "Tape Tickets :: Search Events";
        $em = $this->getEntityManager();
        $searchType = $this->params('type');
        $searchKey = $this->params('searchval');
        $result = array();
        switch ($searchType) {
            case "artist":
                $data = $em->getRepository('Admin\Entity\Event')->getEventsSearch($searchType, $searchKey);
                break;
            case "venues":
                $data = $em->getRepository('Admin\Entity\Event')->getEventsSearch($searchType, $searchKey);
                break;
            case "events":
            default:
                $data = $em->getRepository('Admin\Entity\Event')->getEventsSearch($searchType, $searchKey);
                break;
        }

        $i = 0;
        if (!empty($data)) {
            foreach ($data as $event) {
                switch ($searchType) {
                    case "artist":
                        $result[$i++] = $event->getEventArtist();
                        break;
                    case "venues":
                        $result[$i++] = $event->getEventVenueTitle();
                        break;
                    case "events":
                    default:
                        $result[$i++] = $event->getEventName();
                        break;
                }
            }
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Displays Events related to particular category
     * @author Aditya
     */
    public function eventsbycategoryAction() {
        $this->layout()->pageTitle = " Category Events";
        return array('events' => $tmpevent, 'category' => $catarr);
    }

    /**
     * checkout- Action for the checkout of user
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function checkoutAction() {
        $request = $this->getRequest();
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout";
        $anyone_session = new Container('anyone');
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $em = $this->getEntityManager();
        //if (empty($userId)) {
        /* if not logged in redirect the user to login page */
        //return $this->redirect()->toRoute('home');
        //$userId = 34;
        //} //else {
        if ((!empty($userId)) || ($request->isPost())) {
            $postedData = [];
            $checkoutContainer = new Container("eventcheckout");
            if ($request->isPost()) {
                $postedData = $request->getPost();
                $user_session->eventArray = $postedData; //add to the session
                $checkoutContainer->pdata = $postedData;
            }
            if (empty($userId)) {
                $userId = 34;
                $anyone_session->eventsArray = $postedData;
            }
            if (empty($postedData)) {
                if (empty($anyone_session->eventsArray)) {
                    return $this->redirect()->toRoute('home');
                }
                $postedData = $anyone_session->eventsArray;
                $checkoutContainer->pdata = $postedData;
                unset($anyone_session->eventsArray); //unset event Array - added by Yesh
            }
            //echo '<pre>';
            //print_r(array(
            //    '$request->isPost()' => $request->isPost(),
            //    '$userId' => $userId,
            //    '$postedData' => $postedData
            //));
            //echo '</pre>';
            $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
            $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userId));
            if (empty($userObj)) {
                return $this->url()->fromRoute('checkouterror', array('eventId' => $postedData['eventId']));
            }
            $userCardDetails = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('user' => $userObj));
            $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user' => $userObj));
            if (!empty($billingObj)) {
                $billingObj = $billingObj[0];
                $country = $billingObj->getCountry();
            } else {
                $country = "";
            }
            $commonPlugin = $this->Common();
            $basePath = $commonPlugin->getBasePathOfProj();
            $form = new Forms\CheckoutForm($userCardDetails, $em, $country, $basePath);
            $form->get('email')->setValue($userObj->getEmail());
            $form->get('phoneno')->setValue($userObj->getPhone());
            if (!empty($billingObj)) {
                $fname = $billingObj->getFirstName();
                $lname = $billingObj->getLastName();
                $street_addr = $billingObj->getAddress();
                $country = $billingObj->getCountry();
                $city = $billingObj->getCity();
                $form->get('firstname')->setValue($fname);
                $form->get('lastname')->setValue($lname);
                $form->get('streetaddress')->setValue($street_addr);
                if ($country != "") {
                    $form->get('country')->setValue($country);
                }
                if ($city != "") {
                    $form->get('city')->setValue($city);
                }
            }
            $scheduleId = $postedData['scheduleId'];
            $eventData = array();
            if (!empty($eventObj)) {
                //$eventData['id'] = $eventObj->getId();
                $postedData['eventName'] = $eventObj->getEventName();
                //$eventData['eventArtist'] = $eventObj->getEventArtist();
                //$eventData['eventDesc'] = $eventObj->getEventDesc();
                $postedData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
                //$eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
                //$eventData['eventImageBig'] = $eventObj->getEventImageBig();
                //$eventData['eventLink'] = $eventObj->getEventLink();
                //$eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
                //$eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
                // $eventData['eventAddress'] = $eventObj->getEventAddress();
                //$eventOption = $eventObj->getEventOption();
                //$eventData['eventOption'] = array();
                //$dataArr = array();
                //if (!empty($eventOption)) {
                // $i = 0;
                //foreach ($eventOption as $option) {
                //$dataArr[$i++] = $option->getOption()->getId();
                //}
                //}
                //$eventData['eventOption'] = $dataArr;
                //$eventData['eventSchedule'] = $eventObj->getEventSchedule();
                //$eventData['latitude'] = $eventObj->getLatitude();
                //$eventData['longitude'] = $eventObj->getLongitude();
            }
            $selectedSeats = json_decode($postedData['selectedSeats']);
            $num = 0;
            foreach ($selectedSeats as $select) {
                foreach ($select->seatIds as $seatIds) {
                    $num ++;
                }
            }
            $form->get('quantity')->setValue($num);
        } else {
            return $this->url()->fromRoute('checkouterror', array('eventId' => $postedData['eventId']));
        }
        //}
        return new ViewModel(array(
            'scheduleId' => $scheduleId,
            'userId' => $userId,
            'userData' => $em->getRepository('Admin\Entity\Users')->getUserById($userId),
            'checkoutContainer' => $checkoutContainer->pdata,
            'selectedSeats' => $selectedSeats,
            'form' => $form,
        ));
    }

    public function getcitiesAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $countryId = $this->params('countryId');
        $city_str = "<option value=''>--- Select City ---</option>";
        if (($countryId == "") || ($countryId == 0)) {
            echo $city_str;
            die;
        }
        $CityObj = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => $countryId));
        foreach ($CityObj as $val) {
            $city_str .= '<option value="' . $val->getid() . '">' . $val->getCityName() . '</option>';
            //$selectData[$val->getid()] = $val->getCityName();
        }
        echo $city_str;
        die;
    }

    /**
     * checkoutinner- Action for the middle content of the checkout page as this is opening up in iframe
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function checkoutinnerAction() {
        $viewModel = new ViewModel();
        $this->layout()->pageTitle = " Checkout";
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $request = $this->getRequest();
        if (empty($userId)) {
            /* if not logged in redirect the user to login page */
            //return $this->redirect()->toRoute('home');
            die('Please login to continue.');
        } else {
            $checkoutContainer = new Container("eventcheckout");
            //echo "<pre>"; print_r($checkoutContainer->pdata);die;
            $postedData = $checkoutContainer->pdata;
            //$checkoutContainer->setExpirationSeconds(60);
            $em = $this->getEntityManager();
            //print_r($postedData);
            $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
            $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userId));
            if (empty($userObj)) {
                die('User not found.');
            }
            $userCardDetails = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('user' => $userObj));
            $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user' => $userObj));
            if (!empty($billingObj)) {
                $billingObj = $billingObj[0];
                $country = $billingObj->getCountry();
            } else {
                $country = "";
            }
            $commonPlugin = $this->Common();
            $basePath = $commonPlugin->getBasePathOfProj();
            $form = new Forms\CheckoutForm($userCardDetails, $em, $country, $basePath);
            $form->get('email')->setValue($userObj->getEmail());
            $form->get('phoneno')->setValue($userObj->getPhone());
            if (!empty($billingObj)) {
                $fname = $billingObj->getFirstName();
                $lname = $billingObj->getLastName();
                $street_addr = $billingObj->getAddress();
                $country = $billingObj->getCountry();
                $city = $billingObj->getCity();
                $form->get('firstname')->setValue($fname);
                $form->get('lastname')->setValue($lname);
                $form->get('streetaddress')->setValue($street_addr);
                if ($country != "") {
                    $form->get('country')->setValue($country);
                }
                if ($city != "") {
                    $form->get('city')->setValue($city);
                }
            }
            $eventData = array();
            if (!empty($eventObj)) {
                $eventData['id'] = $eventObj->getId();
                $eventData['eventName'] = $eventObj->getEventName();
                $eventData['eventArtist'] = $eventObj->getEventArtist();
                $eventData['eventDesc'] = $eventObj->getEventDesc();
                $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
                $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
                $eventData['eventImageBig'] = $eventObj->getEventImageBig();
                $eventData['eventLink'] = $eventObj->getEventLink();
                $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
                $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
                $eventData['eventAddress'] = $eventObj->getEventAddress();
                $eventOption = $eventObj->getEventOption();
                $eventData['eventOption'] = array();
                $dataArr = array();
                if (!empty($eventOption)) {
                    $i = 0;
                    foreach ($eventOption as $option) {
                        $dataArr[$i++] = $option->getOption()->getId();
                    }
                }
                $eventData['eventOption'] = $dataArr;
                $eventData['eventSchedule'] = $eventObj->getEventSchedule();
                $eventData['latitude'] = $eventObj->getLatitude();
                $eventData['longitude'] = $eventObj->getLongitude();
                //$eventData['eventSeat'] = $eventObj->getEventSeat();
                $eventData['eventSeat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
                $ticketTypeArr = array();
                $id = 0;
                foreach ($eventData['eventSeat'] as $eventSeat) {
                    $ticketTypeArr[$id]['name'] = str_replace(" ", "_", $eventSeat->getTicketType());
                    $ticketTypeArr[$id]['price'] = $eventSeat->getSeatPrice();
                    $ticketTypeArr[$id]['currency'] = $eventSeat->getCurrency();
                    $id++;
                }
                $totalQty = 0;
                $bookedTickets = array();
                foreach ($ticketTypeArr as $ticket) {
                    $totalQty += $postedData[$ticket['name']];
                    if ($postedData[$ticket['name']] > 0) {
                        $bookedTickets[] = $ticket['name'];
                    }
                }
            }
            $form->get('quantity')->setValue($totalQty);
            $viewModel->setVariables(array('userId' => $userId,
                        'eventData' => $eventData,
                        'checkoutContainer' => $checkoutContainer->pdata,
                        'totalQty' => $totalQty,
                        'bookedTickets' => $bookedTickets,
                        'ticketTypeArr' => $ticketTypeArr,
                        'form' => $form,
                    ))
                    ->setTerminal(true);
            return $viewModel;
        }
    }

    /**
     * Updated by Yesh
     * @return ViewModel
     */
    public function checkouttimeoutAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout Expire";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
//        $scheduleId = $this->params('scheduleId');
//        $seats = $this->params('seats');
//        echo '<pre>';
//        print_r(array(
//            '$eventId' => $eventId,
//            '$scheduleId' => $scheduleId,
//            '$seats' => $seats
//        ));
//        echo '</pre>';
//        die();
        return new ViewModel(array(
            'eventId' => $eventId,
//            'scheduleId' => $scheduleId,
//            'seats' => $seats
        ));
    }

    public function checkouterrorAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " Checkout Error";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    //Added by Shathish
    public function transactiondeclinedAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " transactiondeclined";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    //Added by Shathish
    public function transactioncancelledAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " transactioncancelled";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    //Added by Shathish
    public function transactionerrorAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = " transactionerror";
        $checkoutContainer = new Container("eventcheckout");
        $checkOutData = $checkoutContainer->pdata;
        if (!empty($checkOutData)) {
            $checkoutContainer->getManager()->getStorage()->clear('pdata');
        }
        $eventId = $this->params('eventId');
        return new ViewModel(array('eventId' => $eventId));
    }

    /**
     * eventdetail - Showing the Details of an Event - updates by Yesh
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function eventdetailAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Event Details";
        $em = $this->getEntityManager();
        $eventId = $this->params('eventId');
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $mainOptions = $em->getRepository('\Admin\Entity\MainOptions')->findAll();
        $eventObj = $em->getRepository('\Admin\Entity\Event')->findOneBy(array('id' => $eventId, 'status' => 1));
        if (empty($eventObj)) {
            return new ViewModel(array('eventData' => array()));
        }
        $eventData = array();
        if (!empty($eventObj)) {
            $eventData['id'] = $eventObj->getId();
            $eventData['eventName'] = $eventObj->getEventName();
            $eventData['eventArtist'] = $eventObj->getEventArtist();
            $eventData['eventDesc'] = $eventObj->getEventDesc();
            $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
            $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
            $eventData['eventImageBig'] = $eventObj->getEventImageBig();
            $eventData['eventLink'] = $eventObj->getEventLink();
            $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
            $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
            $eventData['eventAddress'] = $eventObj->getEventAddress();
            $eventData['latitude'] = $eventObj->getLatitude();
            $eventData['longitude'] = $eventObj->getLongitude();
            $eventOption = $eventObj->getEventOption();
            $eventData['eventOption'] = array();
            $dataArr = array();
            if (!empty($eventOption)) {
                $i = 0;
                foreach ($eventOption as $option) {
                    $dataArr[$i++] = $option->getOption()->getId();
                }
            }
            $eventData['eventOption'] = $dataArr;
            $eventData['eventSchedule'] = $eventObj->getEventSchedule();
        }
        return new ViewModel(array('eventData' => $eventData, 'mainOptions' => $mainOptions, 'userId' => $userId));
    }

    /* Aded by Yesh */

    /**
     * ajax event map Action
     */
    public function ajaxeventmapAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $id;
        $eventId;
        $mapObject;
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventId = $data['eventID'];
            //$layoutID = $data['layoutID']; //not in used, may be future
            $mapObj = $em->getRepository('Admin\Entity\EventMap')->getMapByEventId($eventId);
            //$zoneObj = $em->getRepository('Admin\Entity\MapZone')->getZoneByEventId($eventId);
            foreach ($mapObj as $obj) {
                $id = $obj['id'];
                $eventId = $obj['eventId'];
                $mapObject = $obj['mapObject'];
            }
            $mapObject = unserialize($mapObject); //unserialize object
            print json_encode(array('status' => 'success', 'id' => $id, 'eventId' => $eventId, 'mapObject' => $mapObject));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    /**
     * ajax get event schedule id Action
     */
    public function ajaxgeteventscheduleidAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventID = $data['eventID'];
            $eventDate = $data['eventDate'];
            $scheduleID = $em->getRepository('Admin\Entity\EventSchedule')->getEventScheduleIdByEventDate($eventID, $eventDate);
            print json_encode(array('status' => 'success', 'scheduleID' => $scheduleID));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    /**
     * ajax get available seats Action
     */
    public function ajaxgetavailableseatsAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $seatLabel = $data['clickID'];
            $eventID = $data['eventID'];
            $scheduleID = $data['scheduleID'];
            $zoneTitle = $data['zoneTitle'];
            $zone = $em->getRepository('Admin\Entity\MapZone')->getZoneByTitle($zoneTitle, $eventID);
            $zoneID = $zone['id'];
            $zoneSeats = $em->getRepository('Admin\Entity\ZoneSeats')->getSeatStatus($zoneID, $scheduleID);
            $available = 0;
            if ($seatLabel !== "") {
                foreach ($zoneSeats as $zoneSeat) {
                    if ($seatLabel === $zoneSeat['seatLabel']) {
                        $available = $zoneSeat['seatAvailability'];
                        $em->getRepository('Admin\Entity\ZoneSeats')->updateSelectedSeat($zoneID, $scheduleID, $seatLabel, 1, 0, 0);
                    }
                }
            }
            print json_encode(array('status' => 'success', 'zoneSeats' => $zoneSeats, 'available' => $available));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    /**
     * ajax remove selection Action
     */
    public function ajaxremoveselectionAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventID = $data['eventID'];
            $scheduleID = $data['scheduleID'];
            $titleObj = json_decode($data['titleObj']);
            foreach ($titleObj as $row) {
                $zoneTitle = $row->zoneTitle;
                $zone = $em->getRepository('Admin\Entity\MapZone')->getZoneByTitle($zoneTitle, $eventID);
                $zoneID = $zone['id'];
                $seatIds = $row->seatIds;
                foreach ($seatIds as $seatId) {
                    $seatLabel = $seatId;
                    $em->getRepository('Admin\Entity\ZoneSeats')->unselectZoneSeats($zoneID, $eventID, $scheduleID, $seatLabel);
                }
            }
            print json_encode(array('status' => 'success'));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    /**
     * ajax unselect seat booking Action
     */
    public function ajaxunselectseatbookingAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        if ($request->isPost()) {
            $data = $request->getPost();
            $eventID = $data['eventID'];
            $zoneTitle = $data['zoneTitle'];
            $seatLabel = $data['clickID'];
            $scheduleID = $data['scheduleID'];
            $zone = $em->getRepository('Admin\Entity\MapZone')->getZoneByTitle($zoneTitle, $eventID);
            $zoneID = $zone['id'];
            $em->getRepository('Admin\Entity\ZoneSeats')->unselectZoneSeats($zoneID, $eventID, $scheduleID, $seatLabel);
            print json_encode(array('status' => 'success'));
            die();
        } else {
            print json_encode(array('status' => 'error'));
            die();
        }
    }

    /* Aded by Yesh */

    /**
     * geteventseatdetailsajaxAction - This is an AJAX action for fetching the 
     * seat details at event detail page.
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function geteventseatdetailsajaxAction() {
        $request = $this->getRequest();
        $viewModel = new ViewModel();
        $user_session = new Container('user');
        $userId = $user_session->userId;
        if ($request->isPost()) {
            $postedData = $request->getPost();
            $em = $this->getEntityManager();
            $eventObj = $em->getRepository('\Admin\Entity\Event')->getEvent($postedData['eventId']);
            $eventData = array();
            if (!empty($eventObj)) {
                $eventData['id'] = $eventObj->getId();
                $eventData['eventName'] = $eventObj->getEventName();
                $eventData['eventArtist'] = $eventObj->getEventArtist();
                $eventData['eventDesc'] = $eventObj->getEventDesc();
                $eventData['eventVenueTitle'] = $eventObj->getEventVenueTitle();
                $eventData['eventVenueIcon'] = $eventObj->getEventVenueIcon();
                $eventData['eventImageBig'] = $eventObj->getEventImageBig();
                $eventData['eventLink'] = $eventObj->getEventLink();
                $eventData['eventCity'] = $eventObj->getEventCity()->getCityName();
                $eventData['eventCountry'] = $eventObj->getEventCountry()->getCountryName();
                $eventData['eventAddress'] = $eventObj->getEventAddress();
                $eventOption = $eventObj->getEventOption();
                $eventData['eventOption'] = array();
                $dataArr = array();
                if (!empty($eventOption)) {
                    $i = 0;
                    foreach ($eventOption as $option) {
                        $dataArr[$i++] = $option->getOption()->getId();
                    }
                }
                $eventData['eventOption'] = $dataArr;
                $eventData['eventSchedule'] = $eventObj->getEventSchedule();
                $eventData['latitude'] = $eventObj->getLatitude();
                $eventData['longitude'] = $eventObj->getLongitude();
                $eventData['event_seat'] = $em->getRepository('Admin\Entity\EventSeat')->findBy(array('event' => $eventObj, 'isDeleted' => 0));
            }
            $viewModel->setVariables(array('postedData' => $postedData, 'eventData' => $eventData, 'userId' => $userId))->setTerminal(true);
            return $viewModel;
        }
        $viewModel->setVariables(array('postedData' => "", 'eventData' => "", 'userId' => $userId))->setTerminal(true);
        return $viewModel;
    }

    public function confirmorderAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Confirm Order";
        $request = $this->getRequest();
        $user_session = new Container('user');
        $userId = $user_session->userId;
        //$id = $this->params('cardid');        
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        if ($request->isPost()) {
            $checkoutContainer = new Container("eventcheckout");
            $data = $checkoutContainer->pdata;
            if (empty($data)) {
                die("Requested Data not found.");
            } else {
                //print_r($data);
                $postedData = $request->getPost();
                // print_r($postedData);
                $eventObj = $em->getRepository('Admin\Entity\Event')->findOneBy(array('id' => $data['eventId'], 'status' => 1));
                if (empty($eventObj)) {
                    return $this->redirect()->toRoute('checkouterror', array('eventId' => $data['eventId']));
                }
                $userObj = $em->getRepository('Admin\Entity\Users')->find($userId);
                $commonPlugin = $this->Common();
                $userBookingObj = new Entities\UserBooking();
                $userBookingObj->setUser($userObj);
                $userBookingObj->setEvent($eventObj);
                $userBookingObj->setEventDate(date_create(date("Y-m-d", strtotime($data['eventDate']))));
                $userBookingObj->setEventTime(date_create(date("H:i:s", strtotime($data['eventTime']))));
                $userBookingObj->setBookingSeatCount($postedData['quantity']);
                $userBookingObj->setBookingTotalPrice($data['totalAmount']);
                $userBookingObj->setEmail($postedData['email']);
                $userBookingObj->setPhoneNo($postedData['phoneno']);
                /* $userBookingObj->setCardType($postedData['card_type']);
                  $userBookingObj->setCardNo($postedData['cardno']);
                  $userBookingObj->setExpiryMonth($postedData['month']);
                  $userBookingObj->setExpiryYear($postedData['year']); */
                $userBookingObj->setFirstName($postedData['firstname']);
                $userBookingObj->setLastName($postedData['lastname']);
                $userBookingObj->setStreetAddress($postedData['streetaddress']);
                $userBookingObj->setBookingOrderNo("");
                $userBookingObj->setCity($postedData['city']);
                $userBookingObj->setCountry($postedData['country']);
                $userBookingObj->setStatus(2);
                $userBookingObj->setBookingMadeDate(date_create(date('Y-m-d H:i:s')));
                $em->persist($userBookingObj);
                $em->flush();
                $orderId = $userBookingObj->getId();
                $schemeHost = $commonPlugin->getSchemeHostOfProj();
                /* added by Shehan@ITSthe1 - 01022016 */
                // Cash payment order

                if (isset($postedData['cash-box-chk']) && $postedData['cash-box-chk'] == 1) {
                    /* if paid by Cash, bypass the Payment Gateway and redirected to the ticket preview */
                    $ticketPreviewPath = $this->url()->fromRoute('ticketpreview', array('bookingid' => $orderId));
                    $redirectUrl = $schemeHost . $ticketPreviewPath;
                    $userBookingObj->setPayId('CASH');
                    $userBookingObj->setStatus(1);
                    $em->persist($userBookingObj);
                    $em->flush();
                    $this->updateUserSelectedSeats($orderId);
                    $user_session->orderStatus = TRUE;
                    unset($user_session->eventArray);
                    return $this->redirect()->toRoute('ticketpreview', array('bookingid' => $orderId));
                }

                /* Shehan@ITSthe1 */
                $htpconfirmatinPath = $this->url()->fromRoute('htpconfirmation');
                $acceptUrl = $schemeHost . $htpconfirmatinPath;
                $conf = array();
                $securityKey = "bharathikannama123#";
                $conf['accountPspId'] = "testclassic";
                $conf['parametersAcceptUrl'] = $acceptUrl;
                $conf['parametersExceptionUrl'] = $acceptUrl;
                $conf['paymentMethod'] = "CreditCard";
                $conf['layoutLanguage'] = "en_EN";
                $conf['aliasId'] = "";
                $conf["aliasOrderId"] = $orderId;
                $conf["aliasStorePermanently"] = "N";
                if ($conf["aliasId"] == "") {
                    $paramString = "ACCOUNT.PSPID=" . $conf['accountPspId'] . $securityKey . "ALIAS.ORDERID=" . $conf['aliasOrderId'] . $securityKey . "ALIAS.STOREPERMANENTLY=" . $conf["aliasStorePermanently"] . $securityKey . "CARD.PAYMENTMETHOD=" . $conf['paymentMethod'] . $securityKey . "LAYOUT.LANGUAGE=" . $conf['layoutLanguage'] . $securityKey . "PARAMETERS.ACCEPTURL=" . $conf['parametersAcceptUrl'] . $securityKey . "PARAMETERS.EXCEPTIONURL=" . $conf['parametersExceptionUrl'] . $securityKey;
                } else {
                    //$paramstring = "ACCOUNT.PSPID=testclassicclassicinformatics123#ALIAS.ALIASID=C2BCA5D2-35DF-4BBD-A68E-E924F4BD5515classicinformatics123#ALIAS.ORDERID=1459classicinformatics123#CARD.PAYMENTMETHOD=CreditCardclassicinformatics123#LAYOUT.LANGUAGE=en_ENclassicinformatics123#PARAMETERS.ACCEPTURL=http://tapetickets.demos.classicinformatics.com/tmp/index.phpclassicinformatics123#PARAMETERS.EXCEPTIONURL=http://tapetickets.demos.classicinformatics.com/tmp/index.phpclassicinformatics123#";
                    $paramString = "ACCOUNT.PSPID=" . $conf['accountPspId'] . $securityKey . "ALIAS.ALIASID=" . $conf['aliasId'] . $securityKey . "ALIAS.ORDERID=" . $conf['aliasOrderId'] . $securityKey . "ALIAS.STOREPERMANENTLY=" . $conf["aliasStorePermanently"] . $securityKey . "CARD.PAYMENTMETHOD=" . $conf['paymentMethod'] . $securityKey . "LAYOUT.LANGUAGE=" . $conf['layoutLanguage'] . $securityKey . "PARAMETERS.ACCEPTURL=" . $conf['parametersAcceptUrl'] . $securityKey . "PARAMETERS.EXCEPTIONURL=" . $conf['parametersExceptionUrl'] . $securityKey;
                }
                $conf['sha1'] = sha1($paramString);
                $conf['sha1'] = strtoupper($conf['sha1']);
            }
        }
        return new ViewModel(array('orderId' => $orderId, 'postedData' => $postedData, 'userObj' => $userObj, 'data' => $data, 'conf' => $conf));
    }

    //Update by Sathish
    public function htpconfirmationAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Confirm Order";
        $checkoutContainer = new Container("eventcheckout");
        $commonPlugin = $this->Common();
        $data = $checkoutContainer->pdata;
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        if (empty($data)) {
            die("Requested Data not found.");
        } else {
            $postData = $this->params()->fromPost();
            //print_r($postData);
            $getData = $this->params()->fromQuery();
            //print_r($getData);
            $requestData = array_merge($postData, $getData);
            $checkoutContainer->htpresponse = $requestData;

            /*
             * HTP(Host Tokenization Page) response example
             * Array ( [status] => 0 [OrderID] => 39 [NCError] => 0 [NCErrorCN] => 0 
             * [NCErrorCardNo] => 0 [NCErrorCVC] => 0 [NCErrorED] => 0 [CardNo] => XXXXXXXXXXXX0002 
             * [Alias] => B9113DCA-768A-4392-B0D5-ADE35E3194E9 [Brand] => VISA [CN] => testcard 
             * [CVC] => XXX [ED] => 0317 [StorePermanently] => Y 
             * [SHASign] => 4F0CA4137050115FEAE1985E8D3788BD91DB9A25 )
             */

            /* Result of the alias creation:
              0=OK
              1=NOK
              2=Alias updated
              3=Cancelled by user
             */
            if (isset($requestData['Alias_Status']) && $requestData['Alias_Status'] == 3) {
                return $this->redirect()->toRoute('transactioncancelled', array('eventId' => $requestData['eventId'],));
            }
            if ($requestData['status'] == 0 || $requestData['status'] == 3) {
                /* If Alias creation is successful then we need to send it to 2nd request */
                $user_session = new Container('user');
                $userId = $user_session->userId;
                $checkoutContainer = new Container("eventcheckout");
                $checkPageData = $checkoutContainer->pdata;
                $em = $this->getEntityManager();
                $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')->findOneBy(array('user' => $userId, 'event' => $checkPageData['eventId'], 'id' => $requestData['OrderID']));
                $userBookingObj->setCardType($requestData['Brand']);
                $userBookingObj->setCardNo($requestData['CardNo']);

                /*
                  Commenting this code here as no need to save card month year here
                  $ed = $requestData['ED'];
                  $ed_m = substr($ed,0,2);
                  $ed_y = substr($ed,2,2);
                  $userBookingObj->setExpiryMonth($ed_m);
                  $userBookingObj->setExpiryYear($ed_y);
                 */
                $em->flush();
                $schemeHost = $commonPlugin->getSchemeHostOfProj();
                $paymentGatewayReturnPath = $this->url()->fromRoute('paymentgatewayreturn');
                $acceptUrl = $schemeHost . $paymentGatewayReturnPath;
                $amount = $data['totalAmount'] * 100;

                $postData = array(
                    "ACCEPTURL" => $acceptUrl,
                    "ALIAS" => $requestData['Alias'],
                    "AMOUNT" => $amount,
                    "COM" => "TicketOrderId" . $requestData['OrderID'] . "confirmed",
                    "CURRENCY" => "AED",
                    //"CVC"       => "123", /* Need to make it dynamic */
                    "DECLINEURL" => $acceptUrl,
                    "EMAIL" => $userBookingObj->getEmail(),
                    "EXCEPTIONURL" => $acceptUrl,
                    "FLAG3D" => "Y",
                    "HTTP_ACCEPT" => "*/*",
                    "HTTP_USER_AGENT" => "Mozilla/4.0",
                    "ORDERID" => $requestData['OrderID'],
                    "PSPID" => 'testclassic',
                    "PSWD" => "jaahnav123#",
                    "REMOTE_ADDR" => $_SERVER['REMOTE_ADDR'],
                    "RTIMEOUT" => 30,
                    "USERID" => "manugarg",
                    "WIN3DS" => "MAINW",
                );
                $singleString = "";
                $secretKey = "bharathikannama123#";
                foreach ($postData as $key => $value) {
                    $singleString .= $key . '=' . $value . $secretKey;
                }
                $sha1String = sha1($singleString);
                $sha1String = strtoupper($sha1String);
                //echo $sha1String."<br>";
                $postData["SHASIGN"] = $sha1String;
                $fields_string = '';
                foreach ($postData as $key => $value) {
                    $fields_string .= strtoupper($key) . '=' . $value . '&';
                }
                $fields_string = rtrim($fields_string, '&');
                $cSession = curl_init();
                //step2
                curl_setopt($cSession, CURLOPT_URL, "https://secure.payfort.com/ncol/test/orderdirect.asp");
                curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($cSession, CURLOPT_POST, true);
                curl_setopt($cSession, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($cSession, CURLOPT_HEADER, false);
                //step3
                $result = curl_exec($cSession);
                //step4
                curl_close($cSession);
                //step5
                //print_r($result);
                $xml = simplexml_load_string($result);
//                print_r($xml);
//                die();
                if ($xml) {
                    $checkoutContainer->paymentconfirmation = array(
                        "ORDERID" => (string) $xml['orderID'],
                        "PAYID" => (string) $xml['PAYID'],
                        "NCSTATUS" => (string) $xml['NCSTATUS'],
                        "NCERROR" => (string) $xml['NCERROR'],
                        "ACCEPTANCE" => (string) $xml['ACCEPTANCE'],
                        "STATUS" => (string) $xml['STATUS'],
                        "IPCTY" => (string) $xml['IPCTY'],
                        "CCCTY" => (string) $xml['CCCTY'],
                        "ECI" => (string) $xml['ECI'],
                        "CVCCheck" => (string) $xml['CVCCheck'],
                        "AAVCheck" => (string) $xml['AAVCheck'],
                        "VC" => (string) $xml['VC'],
                        "AMOUNT" => (string) $xml['amount'],
                        "CURRENCY" => (string) $xml['currency'],
                        "PM" => (string) $xml['PM'],
                        "BRAND" => (string) $xml['BRAND'],
                        "NCERRORPLUS" => (string) $xml['NCERRORPLUS'],
                    );
                    $htmlstatus = (string) $xml['STATUS'];
                    if ((string) $xml['NCERRORPLUS'] == "CARD REFUSED") {
                        return $this->redirect()->toRoute('transactiondeclined', array('eventId' => $checkPageData['eventId'],));
                    }

                    $htmlData = '';
                    if ($htmlstatus == "9") {
                        return $this->redirect()->toRoute('paymentgatewayreturn', array('shasign' => $getData['SHASign'],));
                    } else if ($htmlstatus == "46") {
                        foreach ($xml as $child => $value) {
                            if ($child == "HTML_ANSWER") {
                                /* If the card supports the 3D verification then this condition will run */
                                $htmlData = base64_decode($value);
                                $viewModel->setVariables(array('htmlData' => $htmlData));
                                return $viewModel;
                            }
                        }
                    }
                } else {
                    return $this->redirect()->toRoute('transactionerror', array('eventId' => $checkPageData['eventId'],));
                }
            } else {
                return $this->redirect()->toRoute('transactionerror', array('eventId' => $checkPageData['eventId'],));
            }
        }
    }

    //get Shaw hash value of parmaeters strings
    //sathish
    protected function getRawSHA($parameters, $passphrase) {
        ksort($parameters);
        // string to be encoded
        $params = array();
        unset($parameters['SHASIGN']);
        // add required params to our digest
        foreach ($parameters as $key => $value) {
            if ($value != '') {
                $params[$key] = mb_strtoupper($key) . '=' . $value;
            }
        }
        // alphabetically, based on keys
        ksort($params);
        // add secret key and return
        return implode($passphrase, $params) . $passphrase;
    }

    //get Shaw of strings
    //sathish
    public function getSHA1($sha) {
        return mb_strtoupper(sha1($sha));
    }

    /**
     * update User Selecte dSeats
     * @param type $bookingId
     * Added by Yesh
     */
    private function updateUserSelectedSeats($bookingId) {
        $em = $this->getEntityManager();
        $user_session = new Container('user');
        $selectedSeats = json_decode($user_session->eventArray['selectedSeats']);
        foreach ($selectedSeats as $row) {
            $zoneTitle = $row->zoneTitle;
            $mapZoneObj = $em->getRepository('Admin\Entity\MapZone')->findOneBy(array('zoneTitle' => $zoneTitle, 'eventId' => $user_session->eventArray['eventId']));
            $zoneId = $mapZoneObj->getId(); //get zone Id
            $seatIds = $row->seatIds;
            foreach ($seatIds as $seatLabel) {
                $zoneSeatsObj = $em->getRepository('Admin\Entity\ZoneSeats')->findOneBy(array(
                    'scheduleId' => $user_session->eventArray['scheduleId'],
                    'eventId' => $user_session->eventArray['eventId'],
                    'zoneId' => $zoneId,
                    'seatLabel' => $seatLabel
                ));
                $zoneSeatsObj->setSeatAvailability(2);
                $zoneSeatsObj->setBookingId($bookingId);
                $zoneSeatsObj->setUserId($user_session->userId);
                $em->persist($zoneSeatsObj);
                $em->flush();
            }
        }
    }

    public function orderAction() {
        $em = $this->getEntityManager();
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $checkoutContainer = new Container("eventcheckout");
        $commonPlugin = $this->Common();
        $checkPageData = $checkoutContainer->pdata;
        $htpResponse = $checkoutContainer->htpresponse;
        $paymentConfirmation = $checkoutContainer->paymentconfirmation;
        $schemeHost = $commonPlugin->getSchemeHostOfProj();
        $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
        $redirectUrl = $schemeHost . $ticketPreviewPath;
        if (empty($checkPageData) || empty($htpResponse) || empty($paymentConfirmation)) {
            die("Requested Data not found.");
        } else {
            $postData = $this->params()->fromPost();
            //print_r($postData);
            $getData = $this->params()->fromQuery();
            //print_r($getData);
            $requestData = array_merge($postData, $getData); /* If data received after entering in 3-D */
            $finalResponse = $requestData;
            //print_r($finalResponse);
            //die;
            $this->shaOut = "bharathikannama123#"; //classicinformatics123 - 13012016
            if (isset($finalResponse) && !empty($finalResponse)) {
                $params = $this->getRawSHA(array_change_key_case($finalResponse, CASE_UPPER), $this->shaOut);
                $sha = $this->getSHA1($params);
                $checksign = $finalResponse['SHASIGN'];
                // doublecheck SHA digest
                if ($sha <> $checksign) {
                    echo "Sha Sign in and out does not matches";
                    exit;
                }
            }
            //added by Yesh
            $userBookingObj = $em->getRepository('Admin\Entity\UserBooking')->findOneBy(array('user' => $userId, 'event' => $checkPageData['eventId'], 'id' => $htpResponse['OrderID']));
            $bookingId = $userBookingObj->getId(); //get the booked id
            if (empty($userBookingObj)) {
                unset($user_session->eventArray); //unset event Array - added by Yesh
                $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
            }
            if (!empty($finalResponse)) {
                /* if Card is 3-D means if user is asked for password for the payment */
                $checkoutContainer->finalResponse = $finalResponse;
                if ($finalResponse['STATUS'] != 0 && $finalResponse['STATUS'] != 2) {
                    $ticketPreviewPath = $this->url()->fromRoute('ticketpreview', array('bookingid' => $finalResponse['orderID']));
                    $redirectUrl = $schemeHost . $ticketPreviewPath;
                    $userBookingObj->setPayId($finalResponse['PAYID']);
                    $userBookingObj->setStatus(1);
                    $em->persist($userBookingObj);
                    $em->flush();
                    $this->updateUserSelectedSeats($bookingId);
                    $user_session->orderStatus = TRUE;
                }
            } elseif ($paymentConfirmation['STATUS'] != 0 && $paymentConfirmation['STATUS'] != 2) {
                /* if Card is not 3-D means if user is not asked for password for the payment */
                $ticketPreviewPath = $this->url()->fromRoute('ticketpreview', array('bookingid' => $paymentConfirmation['ORDERID']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
                $userBookingObj->setPayId($paymentConfirmation['PAYID']);
                $userBookingObj->setStatus(1);
                $em->persist($userBookingObj);
                $em->flush();
                $this->updateUserSelectedSeats($bookingId);
                $user_session->orderStatus = TRUE;
            } else {
                unset($user_session->eventArray); //unset event Array - added by Yesh
                $ticketPreviewPath = $this->url()->fromRoute('checkouterror', array('eventId' => $checkPageData['eventId']));
                $redirectUrl = $schemeHost . $ticketPreviewPath;
            }
            $viewModel->setVariables(array('redirectUrl' => $redirectUrl));
        }
        unset($user_session->eventArray); //unset event Array - added by Yesh
        return $viewModel;
    }

}
