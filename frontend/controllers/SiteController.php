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
        /*
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERPWD, "admin:newdown");
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, 'http://crm-generator.ru/api/project.get-all');
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 GTB6');
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

//        $amount    = urlEncode($price);
//        $userName  = urlEncode(SBER_LOGIN);
//        $password  = urlEncode(SBER_PASSWORD);
//        $returnUrl = urlEncode('http://' . $_SERVER['HTTP_HOST'] . '/' . SBER_RETURN_URL);
//        $failUrl   = urlEncode('http://' . $_SERVER['HTTP_HOST'] . '/' . SBER_FAIL_URL);

        $orderNumber = urlEncode(time());
        $language = urlEncode('ru');

//        $myForm = "userName=$userName";
        $myForm = "userName=";
//        $myForm .= "&password=$password";
//        $myForm .= "&orderNumber=$orderNumber";
//        $myForm .= "&amount=$amount";
//        $myForm .= "&returnUrl=$returnUrl";
//        $myForm .= "&failUrl=$failUrl";
//        $myForm .= "&language=$language";

        curl_setopt($curl, CURLOPT_POST, TRUE);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $myForm);

        $data = curl_exec($curl);
        $data = (array)json_decode($data);
*/

        /*
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERPWD, "admin:newdown");
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, 'http://crm-generator.ru/api/price.get-purchase-by-day');
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 GTB6');

        $projectId = urlEncode(13);
        $date      = urlEncode('2017-03-17');

        $myForm = "projectId=$projectId";
        $myForm .= "&day=$date";

        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $myForm);

        $data = curl_exec($curl);
        $data = (array)json_decode($data);
        */

        return $this->render('index');
    }
}
