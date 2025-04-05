<?php
date_default_timezone_set("Asia/Tokyo");

require_once(__DIR__ . "/Config.php");

class Weather
{
  private $tokenId;
  private $channelId;

  public function __construct()
  {
    $this->tokenId = Config::DISCORD_TOKEN;
    $this->channelId = Config::CHANNEL_ID;
  }
  public function main()
  {
    $text = $this->getWeatherInfoTxt();
    $this->sendToDiscord($text);
  }
  /**
   * 天気情報を取得し、必要情報からテキストを作成
   * @return String APIから取得した情報を元に生成したテキスト
   */
  private function getWeatherInfoTxt()
  {
    $baseUrl = 'https://weather.tsukumijima.net/api/forecast/city/';
    //横浜
    $city = '140010';
    $requestUrl = $baseUrl . $city;
    $response = file_get_contents($requestUrl);
    $data = json_decode($response, true);
    $text = '';
    $index = $this->timeCheck();
    if ($data) {
      //日付
      $text .= $data['forecasts'][$index]['date'] . "の天気です。" . PHP_EOL;
      //天気
      $text .= $data['forecasts'][$index]['telop'] . "でしょう。" . PHP_EOL;
      //最高気温と最低気温。取得タイミングによっては最低気温がNULL
      if ($data['forecasts'][$index]['temperature']['min']['celsius']) {
        $text .= "最高気温は" . $data['forecasts'][$index]['temperature']['max']['celsius'] . "度で、最低気温は" . $data['forecasts'][$index]['temperature']['min']['celsius'] . "度でしょう。" . PHP_EOL;
      } else {
        $text .= "最高気温は" . $data['forecasts'][$index]['temperature']['max']['celsius'] . "度でしょう。" . PHP_EOL;
      }
      //お天気の詳細
      $text .= $data['description']['bodyText'];
      return preg_replace("/\n{2,}/", "\n", $text);
    }
  }
  /**
   * 現在hourから、天気情報を取得するための配列インデックスを取得
   * @return int インデックス
   */
  private function timeCheck()
  {
    $today = date("H");
    if ($today >= 12) {
      return 1;
    } else {
      return 0;
    }
  }

  /**
   * 作成したテキストをDiscord宛に送ります
   * @param String お天気テキスト
   */
  private function sendToDiscord($text)
  {
    $url = "https://discord.com/api/v10/channels/{$this->channelId}/messages";

    $headers = [
      "Authorization: Bot {$this->tokenId}",
      "Content-Type: application/json"
    ];

    $data = json_encode([
      "content" => $text
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
      echo "送信完了: {$this->channelId}" . PHP_EOL;
    } else {
      echo "送信失敗: {$httpCode}" . PHP_EOL;
      echo "レスポンス: {$response}" . PHP_EOL;
    }
  }
}
$w = new Weather();
$w->main();
