<?php
//ライブラリの参照
require_once __DIR__ . '/vendor/autoload.php';

//CurlHTTPClientとLINEBotのインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

//署名の検証
$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log("parseEventRequest failed. InvalidSignatureException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log("parseEventRequest failed. UnknownEventTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log("parseEventRequest failed. UnknownMessageTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log("parseEventRequest failed. InvalidEventRequestException => ".var_export($e, true));
}



//おたんじょーび
$tutit_birth = strtotime("2017-02-04 00:00:00");

//今日
$today = strtotime("now");
//カウントダウン（秒）
$cntdown = $tutit_birth - $today;
//画像選択
$num_img = mt_rand(1,5);
$red_img = "https://" . $_SERVER["HTTP_HOST"] . "/img/red" . $num_img . ".jpg";

foreach ($events as $event) {
	//リプライ用
	$reply_token = $event->getReplyToken();
	//ユーザ情報取得
	$profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
	$userId = $profile['userId'];
	$userName = $profile["displayName"];




	//PostbackEventのチェック
	if ($event instanceof \LINE\LINEBot\Event\PostbackEvent) {
		//内容チェック
		$query = $event->getPostbackData();
		if ($query == "end"){
			//胃もたれ
			$message = "誕生日おめでとう！";
			//リプライ
			$bot->replyText($reply_token, $message);
		}else{




			//レッドロブスターbotの表示
			replyButtonsTemplate($bot,
				$event->getReplyToken(),
				"レッドロブスターbot",
				$red_img,
				"レッドロブスターbot",
				"レッドロブスターの画像を送ってあげるね(^ω^)",
				new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
				"1. もっと見せて///", "more"),
				new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
				"2. 胃もたれするわ…", "end")
				);


		}
	//それ以外
	} else {
		//時間による分岐
		if ($tutit_birth > $today + 1800){
			//カウントダウンメッセージ
			$message = "あと" . $cntdown . "秒でぴちぴちの30歳だね！" . "\n" . ">゜)))彡ﾋﾟﾁﾋﾟﾁ";
			$bot->replyText($reply_token, $message);
		} else {

			//レッドロブスターbotの表示
			replyButtonsTemplate($bot,
				$event->getReplyToken(),
				"レッドロブスターbot",
				$red_img,
				"レッドロブスターbot",
				"レッドロブスターの画像を送ってあげるね(^ω^)",
				new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
				"1. もっと欲しい///", "more"),
				new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (
				"2. 胃もたれするわ…", "end")
				);



		}
	}

}

echo "OK";





//画像
function replyImageMessage($bot, $replyToken, $originalImageUrl, $previewImageUrl) {
	$response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
if (!$response->isSucceeded()) {
	error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
	}
}

//Buttonsテンプレート
function replyButtonsTemplate($bot, $replyToken, $alternativeText, $imageUrl, $title, $text, ...$actions) {
	$actionArray = array();
	foreach($actions as $value) {
	array_push($actionArray, $value);
	}
	$builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
	$alternativeText,
	new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder ($title, $text, $imageUrl, $actionArray)
	);
	$response = $bot->replyMessage($replyToken, $builder);
	if (!$response->isSucceeded()) {
	error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
	}
}




?>
