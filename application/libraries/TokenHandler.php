<?php
require APPPATH . '/libraries/JWT.php';
class TokenHandler
{
    //////////The function generate token/////////////
    private $key;

    public function __construct()
    {

        if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == 'fpw.zbit.ltd') {
            $this->key = "zoobit-chandra-fan-poll"; // Use a local secret key for development
        } else {
            $this->key = "zoobit-chandra-fan-poll-production"; // Use a production secret key
        }
    }

    public function GenerateToken($data)
    {
        $jwt = JWT::encode($data, $this->key);
        return $jwt;
    }

    //////This function decode the token////////////////////
    public function DecodeToken($token)
    {
        $decoded = JWT::decode($token, $this->key, array('HS256'));
        $decodedData = $decoded;
        return $decodedData;
    }
}
