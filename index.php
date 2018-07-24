<?php

function pg_connection_string_from_database_url() {
  extract(parse_url($_ENV["DATABASE_URL"]));
  return "user=$user password=$pass host=$host dbname=" . substr($path, 1); # <- you may want to add sslmode=require there too
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
	
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	$data = json_decode($body, true);
	foreach ($data['events'] as $event)
	{
		if ($event['type'] == 'message')
		{
			if($event['message']['type'] == 'text')
			{
				
				// --------------------------------------------------------------- NOTICE ME...
				
				$inputMessage = $event['message']['text'];
				$outputMessage = new TextMessageBuilder($inputMessage);
				
				$result = $bot->replyMessage($event['replyToken'], $outputMessage);
				return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				
				// --------------------------------------------------------------- ...SENPAI!
				
			}
		}
	}

});

$app->post('/addTicket/',function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$id_ = $request->ID;
	$title_ = $request->title;
	$subtitle_ = $request->subtitle;
	
	$result_ = pg_query($pg_conn, "INSERT INTO ticket_tbl(id,title,subtitle) VALUES ('$id_','$title_','$subtitle_')");
	$data = array(
					'error'=>0,
					'message'=>'succesfull'
	);
	echo json_encode($data);
});

$app->get('/delTicket/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	$result_ = pg_query($pg_conn, "DELETE * FROM ticket_tbl WHERE id='$id_'");
	if(pg_num_rows($result_) > 0){
		$response["error"]=0;
		$response["ticket"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["ticket"], $obj);
		}
	}
    echo json_encode($response);
});

$app->get('/ticket/',function($request,$response){

	$result_ = pg_query($pg_conn, "SELECT * FROM ticket_tbl ORDER BY no DESC LIMIT 5");
	echo pg_num_rows(result_);
	
	if(pg_num_rows($result_) > 0){
		$response["error"]=0;
		$response["ticket"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["ticket"], $obj);
		}
	}
    echo json_encode($response);
});

$app->get('/ticket/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	$result_ = pg_query($pg_conn, "SELECT * FROM ticket_tbl WHERE id='$id_'");
	if(pg_num_rows($result_) > 0){
		$response["error"]=0;
		$response["ticket"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["ticket"], $obj);
		}
	}
    echo json_encode($response);
});

$app->post('/addApply/',function($request,$response){
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);
	$id_ = $request->ID;
	$title_ = $request->title;
	$subtitle_ = $request->subtitle;
	
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
	echo json_encode($data);
});

$app->get('/delApply/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	
	$result_ = pg_query($pg_conn, "DELETE * FROM apply_tbl WHERE ID ='$id_'");
	if(pg_num_rows($result_) > 0){
		$response["error"]=0;
		$response["apply"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["apply"], $obj);
		}
	}
    echo json_encode($response);
	
});

$app->get('/apply/',function($request,$response){
		
	$result_ = pg_query($pg_conn, "SELECT * FROM apply_tbl ORDER BY no DESC LIMIT 5");
	echo("sadasdasdasdasd");
	if(pg_num_rows($result_) > 0){
		echo "sini";
		$response["error"]=0;
		$response["apply"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["apply"], $obj);
		}
	}
    echo json_encode($response);
	
});

$app->get('/apply/{ID}',function($request,$response,array $args){
	$id_ = $args['ID'];
	
	$result_ = pg_query($pg_conn, "SELECT * FROM apply_tbl WHERE ID ='$id_'");
	if(pg_num_rows($result_) > 0){
		$response["error"]=0;
		$response["apply"]= array();
		while($obj = pg_fetch_assoc($result_)){
			array_push($response["apply"], $obj);
		}
	}
    echo json_encode($response);
	
});

$app->post('login',function($request,$response){
	
});

$app->run();