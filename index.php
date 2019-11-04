<?php

require __DIR__ . '/vendor/autoload.php';


use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// load config
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// initiate app
$configs =  [
	'settings' => ['displayErrorDetails' => true],
];

function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  return "user=$user password=$pass host=$host dbname=" . substr($path, 1); # <- you may want to add sslmode=require there too
}
# Here we establish the connection. Yes, that's all.
$pg_conn = pg_connect(pg_connection_string_from_database_url());

$app = new Slim\App($configs);

/* ROUTES */
$app->get('/', function ($request, $response) {
	return "Lanjutkan!";
});

$app->post('/', function ($request, $response)
{
	// get request body and line signature header
	$body 	   = file_get_contents('php://input');
	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// log body and signature
	file_put_contents('php://stderr', 'Body: '.$body);

	// is LINE_SIGNATURE exists in request header?
	if (empty($signature)){
		return $response->withStatus(400, 'Signature not set');
	}

	// is this request comes from LINE?
	if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){
		return $response->withStatus(400, 'Invalid signature');
	}

	// init bot
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);
	$data = json_decode($body, true);
	foreach ($data['events'] as $event)
	{
		$message = $event['message']['text'];
		
				if($message =='HELP'){
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
		    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($inputMessage);
			$result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
	}
	

});

// $app->get('/push/{to}/{message}', function ($request, $response, $args)
// {
// 	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
// 	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

// 	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($args['message']);
// 	$result = $bot->pushMessage($args['to'], $textMessageBuilder);

// 	return $result->getHTTPStatus() . ' ' . $result->getRawBody();
// });

/* JUST RUN IT */
$app->run();