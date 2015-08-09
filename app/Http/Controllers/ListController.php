<?php namespace App\Http\Controllers;

header("Content-Type:text/html; charset=utf-8");
use DB;

class ListController extends Controller {

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
		$result = DB::table('VD_data')->join('VD', 'VD_data.VD_ID', '=', 'VD.VD_ID')->groupBy('VD_ID')->select('VD.VD_ID', DB::raw('avg(SpeedAVG) as avgspeed'), 'VD_lat', 'VD_lon')->get();
		$red = array(1 => 'red');
		$blue = array(1 => 'blue');
		$green = array(1 => 'green');
		for ($i=0; $i < sizeof($result); $i++) { 
			# code...
			switch (true) {
				case ($result[$i]->avgspeed <= 30):
					# code...
					$result[$i]->avgspeed = 'red';
					break;
				
				case ($result[$i]->avgspeed <= 50):
					# code...
					$result[$i]->avgspeed = 'yellow';
					break;
				
				case ($result[$i]->avgspeed > 50):
					# code...
					$result[$i]->avgspeed = 'green';
					break;

			}
		}
		return view('list')->with('result', $result);
	}

}
