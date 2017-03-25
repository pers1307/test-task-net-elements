<?php
namespace frontend\controllers;

use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
//        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
//        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_URL, 'http://glory.local/api/statistics.get-by-plates');
////        curl_setopt($curl, CURLOPT_URL, '/api/statistics.get-by-plates');
//        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 GTB6');
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//
//        $fromDateTime = urlEncode('2016-09-01');
//        $toDateTime   = urlEncode('2016-09-15');
//
//        $myForm = "**from=$fromDateTime";
//        $myForm .= "&to**=$toDateTime";
//
//        curl_setopt($curl, CURLOPT_POST, false);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $myForm);
//
//        $data = curl_exec($curl);
//        $data = (array)json_decode($data);
//
//        curl_close($curl);

        return $this->render('index');
    }
}
