<?php
  date_default_timezone_set("Asia/Tokyo");
  /**
  * 天気情報を取;;得し、必要情報からテキストを作成
  * @return String APIから取得した情報を元に生成したテキスト
  */
  function getWeatherInfoTxt(){ 
    $baseUrl = 'https://weather.tsukumijima.net/api/forecast/city/';
    $city = '140010';
    $requestUrl = $baseUrl . $city;
    $response = file_get_contents($requestUrl);
    $data = json_decode($response,true);
    $text = '';
    $index = timeCheck();
    if($data){
      //日付
      $text .= $data['forecasts'][$index]['date'] . "の天気です。" . PHP_EOL;
      //天気
      $text .= $data['forecasts'][$index]['telop'] . "でしょう。" . PHP_EOL;
      //最高気温と最低気温。取得タイミングによっては最低気温がNULL
      if($data['forecasts'][$index]['temperature']['min']['celsius']){
        $text .= "最高気温は" . $data['forecasts'][$index]['temperature']['max']['celsius'] . "度で、最低気温は" . $data['forecasts'][$index]['temperature']['min']['celsius'] . "度でしょう。" . PHP_EOL;
      } else {
        $text .= "最高気温は" . $data['forecasts'][$index]['temperature']['max']['celsius'] . "度でしょう。" . PHP_EOL;
      }
      //お天気の詳細
      $text .= $data['description']['bodyText'] . PHP_EOL;
      return $text;
    }
  }
  /**
  * 現在hourから、天気情報を取得するための配列インデックスを取得
  * @return int インデックス
  */
  function timeCheck(){
    $today = date("H");
    if($today>=12){
      return 1;
    }else{
      return 0;
    }
  }
  /**
  * テキスト内容をVoiceBoxを用いて再生する
  * @param String 読み上げるお天気テキスト
  */
  function speakWeatherText($text){
    $speaker_id=1;
    //voiceboxコンテナの起動
    //$command = 'docker run --rm -d -p 127.0.0.1:50021:50021 voicevox/voicevox_engine:cpu-ubuntu20.04-latest';
    //$output = shell_exec($command);
    //$containerId = trim(shell_exec("docker ps -q --filter ancestor=voicevox/voicevox_engine:cpu-ubuntu20.04-latest"));
    //echo $containerId;
    $url_query = "http://127.0.0.1:50021/audio_query?text=" . urlencode($text) . "&speaker=" . $speaker_id;
    $ch = curl_init($url_query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));

    $response = curl_exec($ch);
    curl_close($ch);

    $url_synthesis = "http://127.0.0.1:50021/synthesis?speaker=" . $speaker_id;
    $ch = curl_init($url_synthesis);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
    $audio_data = curl_exec($ch);
    curl_close($ch);

    file_put_contents("voice.wav", $audio_data);
  }
  $text = getWeatherInfoTxt();
  speakWeatherText($text);
