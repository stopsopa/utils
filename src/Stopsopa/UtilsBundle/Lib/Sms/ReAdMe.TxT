Konfiguracja do pracy w silex:


$app['sms'] = $app->share(function() use ($app) {
    $sms = new SmsMultipleOptimizer('https://ssl.smsapi.pl/sms.do', 'user@domain.pl', '9g7699b498e8248rfc5a487801bfa8a5', $veryfirst = array( // numery które trafią na początek listy wysyłania
        '658404797', // szymon
        '659965237',
        //'607181010', // kasia
        '557236186', 
    ));
    $sms
        ->setFrom('Talent Days')
//        ->setNotifyUrl($app['url_generator']->generate('test2'))
        ->setNotifyUrl('http://httpd.pl/test.php')
    ;
    return $sms;
});

// użycie:


$params = [
    '698404897' => ['1-1'], // do czterech parametrów
    '669955237' => ['2-2'],
];


$result = $app['sms']->sendMultipleMessages('Zaczynamy z talent days, twój bilet: [%1%], drugi parametr [%2%]', $params, "city:$id");

