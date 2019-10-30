<?php

function send_message($message){
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('8/HC2Uo9u2Koso3r+X8YCrixkjjGUxMgUs2LZKMIejMSpVBndS1b4GHlBpczAA37Y3mjvGCCkGzHk/8igInfNms1Fzh4z0n7OIGKDwo0+zoLQXYOOAIlMvI8aMgorxs4Y3rSwdU4UucQwbG7VpIIugdB04t89/1O/w1cDnyilFU=');
	$bot = new \LINE\LINEBot($httpClient, ['1fd8dfe9f841a3fa12bc912560c268dd' => '1fd8dfe9f841a3fa12bc912560c268dd']);

	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
	$response = $bot->pushMessage('Cc5572be3e840465a0dbef150eabc209f', $textMessageBuilder);

	echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
}

function get_hash(){
	
	return "";
}

function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  return "user=u$ser password=$pass host=$host dbname=" . substr($path, 1); # <- you may want to add sslmode=require there too
}
# Here we establish the connection. Yes, that's all.
$pg_conn = pg_connect(pg_connection_string_from_database_url());

require 'vendor/autoload.php';

use LINE\LINEBot\SignatureValidator as SignatureValidator;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder as TextMessageBuilder;
foreach (glob("handler/*.php") as $handler){include $handler;}

$dotenv = new Dotenv\Dotenv('env');
$dotenv->load();

$configs =  [
	'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

$app->get('/', function ($request, $response) {
	return "LINE bot SDK - mineBCA active";
});

$app->post('/', function ($request, $response)
{
	$body 	   = file_get_contents('php://input');
	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];
	file_put_contents('php://stderr', 'Body: '.$body);
	
	if (empty($signature)){
		return $response->withStatus(400, 'Signature not set');
	}
	
	if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){
		return $response->withStatus(400, 'Invalid signature');
	}
	
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('8/HC2Uo9u2Koso3r+X8YCrixkjjGUxMgUs2LZKMIejMSpVBndS1b4GHlBpczAA37Y3mjvGCCkGzHk/8igInfNms1Fzh4z0n7OIGKDwo0+zoLQXYOOAIlMvI8aMgorxs4Y3rSwdU4UucQwbG7VpIIugdB04t89/1O/w1cDnyilFU=
');
	$bot = new \LINE\LINEBot($httpClient, ['1fd8dfe9f841a3fa12bc912560c268dd
' => '1fd8dfe9f841a3fa12bc912560c268dd']);

	$data = json_decode($body, true);
	foreach ($data['events'] as $event)
	{
		if ($event['type'] == 'message')
		{
			if($event['message']['type'] == 'text')
			{
				// --------------------------------------------------------------- NOTICE ME...
				$message = $event['message']['text'];
				// if($message =='#HELP'){
				// 		$inputMessage = "
				// 		#STATUS 		: Melihat status permintaan \n
				// 		#STATUS#PENDING : Melihat status permintaan yang pending \n
				// 		#STATUS#DONE    : Melihat status permintaan yang telah selesai \n
				// 		#TEMPLATE1 		: Melihat template untuk pengiriman pesan ke merchant \n
				// 		#SEND#TEMPLATE1 : Mengirim pesan dengan menggunakan template 1 \n
				// 		#SEND#TEMPLATE2 : Mengirim pesan dengan menggunakan template 2 \n
				// 		";
				if($message =='#HELP'){
						$inputMessage = "
						#STATUS 						: Melihat status ATM \n
						#STATUS#PENDING 				: Melihat status ATM yang pending \n
						#STATUS#DONE    				: Melihat status ATM yang selesai \n
						#SEND 							: Petunjuk Pengiriman Pesan \n
						#SEND#<kode_atm>#<kode_status> 	: Mengirim pesan status atm \n
						#KODE 							: Melihat kode ATM \n
						";
				}else if($message =='#STATUS'){
						$inputMessage = "
						1. ATM DONE : 10 \n
						2. ATM PENDING : 26 
						";
				}else if($message =='#STATUS#PENDING'){
						$inputMessage = "
						1. Hypermart  : EDC Rusak \n
						2. Nusa Mart  : Tambah EDC \n
						3. FoodMe     : EDC Not Connetion \n
						4. Roti Dhiba : EDC Rusak \n
						5. ...
						";
				}else if($message =='#STATUS#DONE'){
						$inputMessage = "
						1. RM. Sederhana : Tambah EDC \n
						2. Tokopedia	 : Tambah EDC \n
						3. JD ID		 : Tambah EDC \n
						4. FoodMe 		 : Tambah EDC \n
						5. ...
						";
				// }else if($message =='#TEMPLATE1'){
				// 		$inputMessage ="
				// 		System kami mencatat mesin EDC anda tidak digunakan dalam dua minggu terakhir, 
				// 		silahkan pilih salah satu alasan menagapa mesin anda tidak digunakan ? \n
				// 		1. Lagi banyak transaksi tunai \n
				// 		2. Lebih sering menggunakan EDC bank lain \n
				// 		3. Mesin EDC Rusak \n
				// 		4. Pengunjung sepi
				// 		";
				// }else if($message =='#SEND#TEMPLATE1'){
				// 		$inputMessage="Pesan  Anda telah di kirim ...";
				// 		$inputMessage="Pesan  Anda telah di kirim ...";
				// }else if($message =='#SEND#TEMPLATE2'){
				// }else{
				// 		$inputMessage="Selamat datang di server mini (merchant identity). untuk pilih bantuan ketik #HELP";
				// }
				}else if($message =='#SEND'){
					$inputMessage = "Untuk mengirim status ATM dengan format '#SEND#<kode_atm>#<1*2*3*4*5*6*7>'\n
					Keterangan : \n
					1. Kebersihan \n
					2. Kertas \n
					3. CardReader \n
					4. Lampu \n
					5. Keypad \n
					6. DVR \n
					7. CCTV \n
					\n
					Angka pada setelah status diganti dengan 'Y' atau 'T'.
					 ";
				}else if(substr($message,0,5) == '#SEND#'){
					$string = explode('#', $message);
					$kode_atm= $string[1];
					$kebersihan= substr($message, -13,12);
					$kertas = substr($message, -11,10);
					$card = substr($message, -9,-8);
					$lampu = substr($message, -7,-6);
					$keypad = substr($message, -5,-4);
					$dvr = substr($message, -3,-1);
					$cctv = substr($message, -1,0);
					$pg_conn = pg_connect(pg_connection_string_from_database_url());
					$result_ = pg_query($pg_conn, "INSERT INTO notifikasi_tbl(kode_atm,kebersihan,kertas,card,lampu,keypad,dvr,cctv) VALUES ('$kode_atm','$kebersihan','$kertas','$card','$lampu','$keypad','$dvr','$cctv')");
					$data = array(
									'error'=>0,
									'message'=>'succesfull'
					);
					$inputMessage = $data;
					
				}else if($message =='#KODE'){
					$pg_conn = pg_connect(pg_connection_string_from_database_url());
					$result_ = pg_query($pg_conn, "SELECT kode_atm FROM atm_tbl ");
					$response = array();
					$no=0;
					while($obj = pg_fetch_assoc($result_)){
						$no=$no+1;
						$Message = $no.'. '.$obj['kode_atm'];
						$inputMessage = $inputMessage.' \n '.$Message;
					}
					pg_close(pg_connection_string_from_database_url());
				}else{
						$inputMessage="Selamat datang. Untuk pilih bantuan ketik #HELP";
				}		
				$outputMessage = new TextMessageBuilder($inputMessage);
				
				$result = $bot->replyMessage($event['replyToken'], $outputMessage);
				return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				
				// --------------------------------------------------------------- ...SENPAI!
				
			}
		}
	}

});

$app->post('/addCheck/',function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$id_ = $request->ID;
	$title_ = $request->title;
	$subtitle_ = $request->subtitle;
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "INSERT INTO ticket_tbl(id,title,subtitle) VALUES ('$id_','$title_','$subtitle_')");
	$data = array(
					'error'=>0,
					'message'=>'succesfull'
	);
	pg_close(pg_connection_string_from_database_url());
	echo json_encode($data);
	send_message('Hypermart mengirim pesan. Ganguan : '.$title_.'. Pesan -> '.$subtitle_);
});
// Ini function untuk mini (merchan identity)
$app->post('/addTicket/',function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$id_ = $request->ID;
	$title_ = $request->title;
	$subtitle_ = $request->subtitle;
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "INSERT INTO ticket_tbl(id,title,subtitle) VALUES ('$id_','$title_','$subtitle_')");
	$data = array(
					'error'=>0,
					'message'=>'succesfull'
	);
	pg_close(pg_connection_string_from_database_url());
	echo json_encode($data);
	send_message('Hypermart mengirim pesan. Ganguan : '.$title_.'. Pesan -> '.$subtitle_);
});

$app->get('/delTicket/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "DELETE * FROM ticket_tbl WHERE id='$id_'");
	if(pg_num_rows($result_) > 0){
		$response = array();
		$response["error"]=0;
		$response["ticket"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["ticket"], $obj);
		}
	}
	pg_close(pg_connection_string_from_database_url());
    echo json_encode($response);
});

$app->get('/ticket/',function($request,$response){
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "SELECT * FROM ticket_tbl ORDER BY no DESC LIMIT 10");
	$response = array();
	$response["error"]=0;
	$response["ticket"]= array();
	while($obj = pg_fetch_assoc($result_)){
		array_push($response["ticket"], $obj);
	}
	pg_close(pg_connection_string_from_database_url());
    echo json_encode($response);
});

$app->get('/ticket/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "SELECT * FROM ticket_tbl WHERE id='$id_'");
	if(pg_num_rows($result_) > 0){
		$response = array();
		$response["error"]=0;
		$response["ticket"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["ticket"], $obj);
		}
	}
	pg_close(pg_connection_string_from_database_url());
    echo json_encode($response);
});

$app->post('/addApply/',function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$id_ = $request->ID;
	$title_ = $request->title;
	$subtitle_ = $request->subtitle;
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$query_ = "INSERT INTO apply_tbl(id,title,subtitle) VALUES ('$id_','$title_','$subtitle_')";
	if(pg_query($pg_conn,$query_)){
		$message_ = "successfully";
	}else{
		$message_ = "unsuccessful";
	}
	
	$data = array(
					'error'=>0,
					'message'=>$message_
	);
	pg_close(pg_connection_string_from_database_url());
	echo json_encode($data);
	send_message('Hypermart mengirim pesan. Permintaan : '.$title_.'. Pesan -> '.$subtitle_);
});	

$app->get('/delApply/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "DELETE * FROM apply_tbl WHERE ID ='$id_'");
	if(pg_num_rows($result_) > 0){
		$response = array();
		$response["error"]=0;
		$response["apply"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["apply"], $obj);
		}
	}
	pg_close(pg_connection_string_from_database_url());
    echo json_encode($response);
	
});

$app->get('/apply/',function($request,$response){
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "SELECT * FROM apply_tbl ORDER BY no DESC LIMIT 10");
	if(pg_num_rows($result_) > 0){
		$response = array();
		$response['errors']=0;
		$response['apply']=array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response['apply'], $obj);
		}
	}
	pg_close(pg_connection_string_from_database_url());
    echo json_encode($response);
	
});

$app->get('/apply/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$result_ = pg_query($pg_conn, "SELECT * FROM apply_tbl WHERE ID ='$id_'");
	if(pg_num_rows($result_) > 0){
		$response = array();
		$response["error"]=0;
		$response["apply"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["apply"], $obj);
		}
	}
	pg_close(pg_connection_string_from_database_url());
    echo json_encode($response);
	
});

$app->post('/login/',function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$username_ = $request->username;
	$password_ = md5($request->password);
	$result_ = pg_query($pg_conn, "SELECT 1 FROM user_tbl WHERE username='$username_' AND password='$passsword_'");
	if(pg_num_rows($result_) > 0){
		$message_ = "successfully";
	}else{
		$message_ = "unsuccessful";
	}
	$data = array(
		'error'=>0,
		'message'=>$message_
	);
	pg_close(pg_connection_string_from_database_url());
	echo json_encode($data);
});

$app->post('/register/', function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$email_ = $request->email;
	$norek_ = $request->no_rek;
	$nohp_ = $request->no_hp;
	$username_ = $request->title;
	$password_ = md5($request->subtitle);
	$pg_conn = pg_connect(pg_connection_string_from_database_url());
	$query_ = "INSERT INTO user_tbl(email,no_rek,no_hp,username,password,qversion,qid) VALUES ('$email_','$norek_','$nohp_','$username_','$password_')";
		if(pg_query($pg_conn,$query_)){
			$message_ = "successfully";
		}else{
			$message_ = "unsuccessful";
		}
		
	$data = array(
		'error'=>0,
		'message'=>$message_
	);
	pg_close(pg_connection_string_from_database_url());
	echo json_encode($data);
});

$app->run();