

## 商家

1. `java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i doc_resources/merchant/merchant.yaml -l swagger -o public/remote/merchant`



CI 脚本

```
cd /var/www/html/memecoinsapi
sed -i "s|- http|- https|" yamls/common.yaml && sed -i "s|office.techrare.com:5681|office.techrare.com|" yamls/main.yaml
sed -i "s|- http|- https|" doc_resources/merchant/common.yaml && sed -i "s|localhost:8082|office.techrare.com|" doc_resources/merchant/main.yaml
sed -i "s|- http|- https|" yamls/generalize/common.yaml
/usr/src/node-v8.11.1-linux-x64/bin/swagger-merger -i ./yamls/main.yaml -o ./yamls/swagger.yaml && java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i yamls/swagger.yaml -l swagger -o public/remote
/usr/src/node-v8.11.1-linux-x64/bin/swagger-merger -i ./doc_resources/merchant/main.yaml -o ./doc_resources/merchant/swagger.yaml && java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i doc_resources/merchant/swagger.yaml -l swagger -o public/remote/merchant
/usr/src/node-v8.11.1-linux-x64/bin/swagger-merger -i ./yamls/generalize/main.yaml -o ./yamls/generalize/swagger.yaml && java -jar /opt/swagger-codegen-cli-2.3.1.jar generate -i yamls/generalize/swagger.yaml -l swagger -o public/remote/generalize
sed -i "s|http://office.techrare.com:5681|https://office.techrare.com|" public/swagger/index.html
sed -i "s|/memecoinsapi|https://office.techrare.com/memecoinsapi|" public/swagger/generalize.html
sed -i "s|https://localhost:8082|https://office.techrare.com|" public/swagger/merchant.html
```

```
cd /var/www/html/memecoins
sed -i "s|office.techrare.com/memecoinsapi/public|services.memecoins.com.tw/memecoins/public/|" app/Api/Merchant/Services/ImageToolsService.php
sed -i "s|192.168.1.80|127.0.0.1|" app/Common/Services/MqttNotificationService.php
sed -i "s|'memecoins','/'|'PA8CITB1qSAMLTm1Crtc','/'|" app/Common/Services/MqttNotificationService.php
sed -i "s|office.techrare.com/memecoins-register-h5/#/register/|services.memecoins.com.tw/memecoins-register-h5/index.html#/register/|" app/Api/Merchant/Repositories/StoreRepository.php
sed -i "s|office.techrare.com/memecoinsapi|services.memecoins.com.tw/memecoins|" app/Api/Merchant/Services/ImageToolsService.php
sed -i "s|http|https|" app/Api/V1/Controllers/ToolsController.php
sed -i "s|office.techrare.com:5681|services.memecoins.com.tw|" app/Api/V1/Controllers/ToolsController.php
sed -i "s|memecoinsapi|memecoins|" app/Api/V1/Controllers/ToolsController.php
sed -i "s|office.techrare.com/memecoins-register-h5/#/register/|services.memecoins.com.tw/memecoins-register-h5/index.html#/register/|" app/Api/V1/Services/QRCodeServiceImpl.php
```


[網紅Api文檔地址](https://office.techrare.com/memecoinsapi/public/swagger/generalize.html)

##### 添加店家用戶測試用戶

`php artisan db:seed --class=StoreUserSeeder`
`php artisan db:seed --class=StoresSeeder`
`php artisan db:seed --class=MemberTableSeeder`
`php artisan db:seed --class=ActivityTableSeeder`
`php artisan db:seed --class=OpenhourTableSeeder`

`php artisan db:seed --class=StoreDownloadSeeder`
`php artisan db:seed --class=ImageSignSeeder`

`php artisan db:seed --class=RebateTestSeeder`

測試撥數的命令

`php artisan FeatureProbability`

修改表结构

`ALTER TABLE `store_user` CHANGE `permission` `permission` ENUM('ALL','ONLYSEE','NONE') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;`


添加App內頁Url

```
ALTER TABLE `banner` ADD `app_page` VARCHAR(255) NULL COMMENT 'App内页页面' AFTER `url`;
```


```
ce /var/www/html/memecoinsapi
sudo mkdir -p public/upload/avatar
sudo mkdir -p public/upload/notice
sudo chmod 777 public/upload/* -R
```

```
ALTER TABLE `banner` ADD `rank` INT NOT NULL DEFAULT '1' COMMENT '排序' AFTER `app_page`;
ALTER TABLE `store` ADD `status` INT NOT NULL DEFAULT '1' COMMENT '店家状态' AFTER `service`;
```