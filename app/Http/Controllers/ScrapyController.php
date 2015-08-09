<?php namespace App\Http\Controllers;

ini_set('memory_limit', '256M');
ini_set('max_execution_time', '0');
header("Content-Type:text/html; charset=utf-8");

use DB;
include 'simple_html_dom.php';
//include 'async.php';

class ScrapyController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$urls = [];
		$xml = simplexml_load_file('http://traffic.kctmc.nat.gov.tw/xml/vd.xml');
		DB::table('VD_Data')->delete();
		for ($i=0; $i < sizeof($xml); $i++) { 
			# code...
			/*$html = file_get_html('http://traffic.kctmc.nat.gov.tw/VD_view.jsp?vd_id='.$xml->marker[$i]['desc']);
			//echo "VD_ID: ".$xml->marker[$i]['desc']."<br/>";
			foreach ($html->find('td[align=center] table[width=430] tr') as $element) {
				# code...
				if(strpos($element, "車道方向與位置") == false){
					DB::table('VD_Data')->insert(
					    array(
					    	'VD_ID'      => $xml->marker[$i]['desc'],
					    	'Direction'  => $element->find('td', 0)->plaintext,
					    	'SpeedAVG'   => $element->find('td', 1)->plaintext,
					    	'TrafficAVG' => $element->find('td', 2)->plaintext
					    )
					);
				}
			}*/
			//echo "<br/>";
			array_push($urls, 'http://traffic.kctmc.nat.gov.tw/VD_view.jsp?vd_id='.$xml->marker[$i]['desc']);
        }
        $this->async_get_url($urls, $xml);
	}

	public function recordVD()
	{
		$xml = simplexml_load_file('http://traffic.kctmc.nat.gov.tw/xml/vd.xml');
		for ($i=0; $i < sizeof($xml); $i++) { 
			# code...
			echo "VD_ID: ".$xml->marker[$i]['desc'].", ";
			echo "VD_Name: ".$xml->marker[$i]['name'].", ";
			echo "VD_Lat: ".$xml->marker[$i]['lat'].", ";
			echo "VD_Lon: ".$xml->marker[$i]['lng'];
			echo "<br/>";
			DB::table('VD')->insert(
			    array('VD_ID' => $xml->marker[$i]['desc'], 'VD_Name' => $xml->marker[$i]['name'], 'VD_Lat' => $xml->marker[$i]['lat'], 'VD_Lon' => $xml->marker[$i]['lng'])
			);
		}
	}

    public function async_get_url($url_array, $xml, $wait_usec = 0)
    {
        if (!is_array($url_array))
            return false;

        $wait_usec = intval($wait_usec);

        $data    = array();
        $handle  = array();
        $running = 0;

        $mh = curl_multi_init(); // multi curl handler

        $i = 0;
        foreach($url_array as $url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return don't print
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
            curl_setopt($ch, CURLOPT_MAXREDIRS, 7);

            curl_multi_add_handle($mh, $ch); // 把 curl resource 放進 multi curl handler 裡

            $handle[$i++] = $ch;
        }

        /* 執行 */
        /* 此種做法會造成 CPU loading 過重 (CPU 100%)
        do {
            curl_multi_exec($mh, $running);

            if ($wait_usec > 0) // 每個 connect 要間隔多久
                usleep($wait_usec); // 250000 = 0.25 sec
        } while ($running > 0);
        */

        /* 此做法就可以避免掉 CPU loading 100% 的問題 */
        // 參考自: http://www.hengss.com/xueyuan/sort0362/php/info-36963.html
        /* 此作法可能會發生無窮迴圈
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        */
        /*
        // 感謝 Ren 指點的作法. (需要在測試一下)
        // curl_multi_exec的返回值是用來返回多線程處裡時的錯誤，正常來說返回值是0，也就是說只用$mrc捕捉返回值當成判斷式的迴圈只會運行一次，而真的發生錯誤時，有拿$mrc判斷的都會變死迴圈。
        // 而curl_multi_select的功能是curl發送請求後，在有回應前會一直處於等待狀態，所以不需要把它導入空迴圈，它就像是會自己做判斷&自己決定等待時間的sleep()。
        */
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        /* 讀取資料 */
        $count = 0;
        foreach($handle as $i => $ch) {
            $content  = curl_multi_getcontent($ch);
            $data[$i] = (curl_errno($ch) == 0) ? $content : false;
            $html = str_get_html($data[$i]);
            if(!empty($html)){
                foreach ($html->find('td[align=center] table[width=430] tr') as $element) {
                    # code...
                    if(strpos($element, "車道方向與位置") == false){
                        DB::table('VD_Data')->insert(
						    array(
						    	'VD_ID'      => $xml->marker[$i]['desc'],
						    	'Direction'  => $element->find('td', 0)->plaintext,
						    	'SpeedAVG'   => $element->find('td', 1)->plaintext,
						    	'TrafficAVG' => $element->find('td', 2)->plaintext
						    )
						);
                    }
                }
            }
        }

        /* 移除 handle*/
        foreach($handle as $ch) {
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);

        return $data;
    }
}
