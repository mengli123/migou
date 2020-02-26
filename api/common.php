<?php
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

function send_code($mobile){
    AlibabaCloud::accessKeyClient('LTAI4FbhfkkiYAN8JXboHGGw', '2uGUArCmzXyqFPOWV9A1vGWGnTCo9Y')
        ->regionId('cn-hangzhou')
        ->asDefaultClient();
    $code =rand(1000,9999);

    try {
        $result = AlibabaCloud::rpc()
            ->product('Dysmsapi')
    // ->scheme('https') // https | http
            ->version('2017-05-25')
            ->action('SendSms')
            ->method('POST')
            ->host('dysmsapi.aliyuncs.com')
            ->options([
                'query' => [
                    'RegionId' => "cn-hangzhou",
                    'PhoneNumbers' => $mobile,
                    'SignName' => "咪购商城",
                    'TemplateCode' => "SMS_184215635",
                    'TemplateParam' => "{\"code\":\"$code\"}",
                ],
            ])
            ->request();
        if($result['Code']=='OK'){
            return $code;
        }
        print_r($result->toArray());
    } catch (ClientException $e) {
        echo $e->getErrorMessage() . PHP_EOL;
    } catch (ServerException $e) {
        echo $e->getErrorMessage() . PHP_EOL;
    }

}
