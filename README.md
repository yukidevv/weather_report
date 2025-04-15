# ヘビー・ウェザー
お天気情報を取得してdiscordに飛ばします(06:30、21:00)  
Line Notifyのサービス終了に伴う代替用の通知アプリです  
Linux上でsystemd登録することで定期実行を実現します  
何故かPHPで作成。デフォルトは横浜の天気を取得するようにしています

# 使用技術等
- php7.4(docker)
- discord API
- systemd

# お天気情報取得元
- https://weather.tsukumijima.net/

# テスト
```
$ systemctl start wheather.service
```
# 定期実行
```
$ sudo systemctl enable wheather.timer
```

# Config
DiscordのAPIキーを発行し、Config.phpに記載して読み込んでください
