<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            /* 'home' => array(
              'type' => 'Zend\Mvc\Router\Http\Literal',
              'options' => array(
              'route'    => '/admin',
              'defaults' => array(
              'controller' => 'Admin\Controller\Index',
              'action'     => 'index',
              ),
              ),
              ), */
            'adminlogin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/[:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    //'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'login',
                    ),
                ),
            ),
            'adminlogout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/logout',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'logout',
                    ),
                ),
            ),
            'admindashboard' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/dashboard',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'dashboard',
                    ),
                ),
            ),
            'adminchangepassword' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/changepassword',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'changepassword',
                    ),
                ),
            ),
            'adminforgotpassword' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/forgotpassword',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'forgotpassword',
                    ),
                ),
            ),
            'adminsettings' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/settings',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'adminsettings',
                    ),
                ),
            ),
            'addevent' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/add[/:layout_id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'add',
                    ),
                ),
            ),
            //Added by Yesh
            'ajaxprocessupload' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxprocessupload',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxprocessupload',
                    ),
                ),
            ),
            'ajaxsavedrawing' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxsavedrawing',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxsavedrawing',
                    ),
                ),
            ),
            'ajaxeditdrawing' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxeditdrawing',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxeditdrawing',
                    ),
                ),
            ),
            'eventreports' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/eventreports',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'eventreports',
                    ),
                ),
            ),
            'ajaxgeteventlist' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxgeteventlist',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxgeteventlist',
                    ),
                ),
            ),
            'ajaxgeteventreport' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxgeteventreport',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxgeteventreport',
                    ),
                ),
            ),
            'blockrelease' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/blockrelease',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'blockrelease',
                    ),
                ),
            ),
            'ajaxgetareazone' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxgetareazone',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxgetareazone',
                    ),
                ),
            ),
            'ajaxcheckavailability' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxcheckavailability',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxcheckavailability',
                    ),
                ),
            ),
            'ajaxchangeseatstatus' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxchangeseatstatus',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxchangeseatstatus',
                    ),
                ),
            ),
            'ajaxblockreleaseseats' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxblockreleaseseats',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxblockreleaseseats',
                    ),
                ),
            ),
            //Added by Yesh
            'layout' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/layout[/:event_id][/:layout_id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'layout',
                    ),
                ),
            ),
            'layoutnew' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/layoutnew',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'layoutnew',
                    ),
                ),
            ),
            'city' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/common/city',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Common',
                        'action' => 'city',
                    ),
                ),
            ),
            'getlayout' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/common/getlayout',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Common',
                        'action' => 'getlayout',
                    ),
                ),
            ),
            'editevent' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/edit/[:eventId][/:layout_id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'edit',
                    ),
                ),
            ),
            'listevent' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/list',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'list',
                    ),
                ),
            ),
            'eventchangestatus' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/event/changestatus/[:type][/:eventId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'eventchangestatus',
                    ),
                ),
            ),
            'eventdelete' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/event/delete/[:eventId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'eventdelete',
                    ),
                ),
            ),
            'eventcancel' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/event/cancel/[:eventId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'eventcancel',
                    ),
                ),
            ),
            'ajaxeventslist' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/event/ajaxeventslist',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'ajaxeventslist',
                    ),
                ),
            ),
            'users' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/user/list',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'listusers',
                    ),
                ),
            ),
            'ajaxuserslist' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/user/ajaxuserslist',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'ajaxuserslist',
                    ),
                ),
            ),
            'userchangestatus' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/user/changestatus/[:type][/:userId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'userchangestatus',
                    ),
                ),
            ),
            'userdelete' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/user/delete/[:userId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'userdelete',
                    ),
                ),
            ),
            'useredit' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/user/edit/[:userId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'useredit',
                    ),
                ),
            ),
            'useradd' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/user/add/',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'useradd',
                    ),
                ),
            ),
            'admingetcity' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/user/getcity/[:countryId]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'getcity',
                    ),
                ),
            ),
            'addcategory' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/category/add',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'action' => 'add',
                    ),
                ),
            ),
            'categorydelete' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/category/categorydelete[/:catid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'action' => 'delete',
                    ),
                ),
            ),
            'editcategory' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/category/categoryedit[/:catid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'action' => 'edit',
                    ),
                ),
            ),
            'categoryindex' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/category/index',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'action' => 'index',
                    ),
                ),
            ),
            'categorylistingdata' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/category/categorylistingdata',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'action' => 'categorylistingdata',
                    ),
                ),
            ),
            'cmsindex' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/cms/index',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Cms',
                        'action' => 'index',
                    ),
                ),
            ),
            'addcms' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/cms/cms-add',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Cms',
                        'action' => 'add',
                    ),
                ),
            ),
            'cmslistingdata' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/cms/cmslistingdata',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Cms',
                        'action' => 'cmslisting',
                    ),
                ),
            ),
            'editcms' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/cms/cmsedit[/:cmsid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Cms',
                        'action' => 'edit',
                    ),
                ),
            ),
            'emailindex' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/email/index',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Email',
                        'action' => 'index',
                    ),
                ),
            ),
            'emaillistingdata' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin/email/emaillistingdata',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Email',
                        'action' => 'emaillistingdata',
                    ),
                ),
            ),
            'editemail' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/email/edit[/:emailid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Email',
                        'action' => 'editemail',
                    ),
                ),
            ),
            'eventscheduledelete' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/event/delschedule[/:eventscheduleid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Event',
                        'action' => 'eventscheduledelete',
                    ),
                ),
            ),
            'orderhistory' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/orders[/:userid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'userbookinghistory',
                    ),
                ),
            ),
            'orderhistorylist' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/orderhistory[/:userid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'orderhistory',
                    ),
                ),
            ),
            'adminorderdetail' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/order/detail[/:orderid]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'userbookingdetail',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Category' => 'Admin\Controller\CategoryController',
            'Admin\Controller\Cms' => 'Admin\Controller\CmsController',
            'Admin\Controller\Email' => 'Admin\Controller\EmailController',
            'Admin\Controller\Event' => 'Admin\Controller\EventController',
            'Admin\Controller\Common' => 'Admin\Controller\CommonController',
            'Admin\Controller\User' => 'Admin\Controller\UserController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/adminlayout' => __DIR__ . '/../view/layout/adminlayout.phtml',
            'layout/layoutscreen' => __DIR__ . '/../view/layout/layout.phtml',
            'layout/layout-login' => __DIR__ . '/../view/layout/layout-login.phtml',
            'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            'Admin_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Admin/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Admin\Entity' => 'Admin_driver'
                ),
            ),
        ),
        'configuration' => array(
            'orm_default' => array(
                'datetime_functions' => array(
                
                ),
            'numeric_functions' => array(
                
                ),
            'string_functions' => array(
                
                ),
            ),
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
