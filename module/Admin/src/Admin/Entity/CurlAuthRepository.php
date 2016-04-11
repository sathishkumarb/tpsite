<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Admin\Entity;

class CurlAuthRepository extends EntityRepository {

    function curl_function($endpoint, $type, $params, $target = array()) {
        $ch = curl_init();
        $token = $target['token'];
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        if ($type == "POST") {
            if ($target['target'] !== 'auth') {
                $params = json_encode($params);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $fields_string = http_build_query($params);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            }
        } elseif ($type == "GET") {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ));
        }
        //execute post
        $result = curl_exec($ch) or exit(curl_error($ch));
        //close connection
        curl_close($ch);
        return $result;
    }

    //check access token
    public function checkAccessToken($nowTime) {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder('u');
        $query = $query->from('Admin\Entity\CurlAuth', 'u');
        $query = $query->select('count(u.id) as cnt,u.token,u.endTime', 'u');
        $query = $query->where('u.endTime >= :end_time')->setParameter('end_time', $nowTime);
        $query = $query->getQuery();
//        print_r(array(
//            'sql' => $query->getSql(),
//            'parameters' => $query->getParameters(),
//        ));
//        $Result = $query->execute();
        $Result = $query->getSingleResult();
        return $Result;
    }

    //get a new access token
    public function getAccessToken() {
        $endpoint = "https://api.etixdubai.com/oauth2/accesstoken";
        $params = array(
            "client_id" => "27403b7cee244a6e9e24423d087530df",
            "client_secret" => "dd3e7736694640c9b5d561c398efc871",
            "username" => "gettyImages_user_name", //gettyImages_user_name
            "password" => "gettyImages_user_password", //gettyImages_user_password
            "grant_type" => "client_credentials"
        );
        $data = json_decode($this->curl_function($endpoint, "POST", $params, array('target' => 'auth', 'token' => '')));
        return $data;
    }

    //Add a customer
    public function addNewCustomer($str_data, $access_token) {
        $endpoint = 'https://api.etixdubai.com/customers?sellerCode=ATAPE1';
        $params = json_decode($str_data);
        $data = json_decode($this->curl_function($endpoint, "POST", $params, array('target' => 'customer', 'token' => $access_token)));
        return $data;
    }

    //Create new basket
    public function createNewBasket($str_data, $access_token) {
        $endpoint = 'https://api.etixdubai.com/baskets';
        $params = json_decode($str_data);
        $data = json_decode($this->curl_function($endpoint, "POST", $params, array('target' => 'basket', 'token' => $access_token)));
        return $data;
    }

    //add Offers To Basket
    public function addOffersToBasket($str_data, $access_token, $basketID) {
        $endpoint = 'https://api.etixdubai.com/baskets/' . $basketID . '/offers';
        $params = json_decode($str_data);
        $data = json_decode($this->curl_function($endpoint, "POST", $params, array('target' => 'offers', 'token' => $access_token)));
        return $data;
    }

    //Create new order
    public function addNewOrder($str_data, $access_token, $basketID) {
        $endpoint = 'https://api.etixdubai.com/Baskets/' . $basketID . '/purchase';
        $params = json_decode($str_data);
        $data = json_decode($this->curl_function($endpoint, "POST", $params, array('target' => 'order', 'token' => $access_token)));
        return $data;
    }

    //View order
    public function viewOrder($access_token, $OrderID) {
        $endpoint = 'https://api.etixdubai.com/orders/' . $OrderID . '?sellerCode=ATAPE1';
        $data = json_decode($this->curl_function($endpoint, "GET", '', array('target' => 'order', 'token' => $access_token)));
        return $data;
    }

}
