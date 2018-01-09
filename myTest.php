<?php

//инициализация сессии
$ch = curl_init($selenium_url);
$selenium_url = "http://localhost:4444/wd/hub";
$capabilities = array(
    'browserName' => 'firefox',
    'platform' => 'ANY',
    'enableVNC' =>'true'
);
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('desiredCapabilities' => $capabilities,)));
$result = json_decode(curl_exec($ch), true);
$sessionId = $result['sessionId']; //запоминаем сессию

//открываем страницу сайта
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/url");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("url" => "https://www.yandex.ru/")));
curl_exec($ch);

//удаляем куки и создаем куку
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/cookie");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
$cookie = array(
    'name' => null,
    'value' => null,
    'path' => null,
    'domain' => null,
    'expiry' => null,
    'secure' => null,
    'httpOnly' => null,
);
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/cookie");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('cookie' => $cookie,)));
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/cookie");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
$result = curl_exec($ch);

//поиск элемента и клик
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'using' => 'xpath',
    'value' => '//*[@id="tabnews_news"]/h1/a')));
$result = json_decode(curl_exec($ch), true);
$id = $result['value']['element-6066-11e4-a52e-4f735466cecf']; //константа элемента
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element/$id/click");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_exec($ch);

////проверяем, что открылась страница новостей
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/title");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
$result = json_decode(curl_exec($ch), true);
$title = $result['value'];
$title1 = "Яндекс.Новости: Главные новости сегодня, самые свежие и последние новости России онлайн"; //ожидаем увидеть
if ($title != $title1) {
    echo "Test failed. The title is '" . $title . "'\n";
    }
curl_exec($ch);

////возвращаемся на главную
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/back");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_exec($ch);

//задаем запрос для поиска в поисковой строке
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'using' => 'id',
    'value' => 'text')));
$result = json_decode(curl_exec($ch), true);

$id = $result['value']['element-6066-11e4-a52e-4f735466cecf'];
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element/$id/value");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'value' => array('s', 'e', 'l', 'e', 'n', 'i', 'u', 'm'))));
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'using' => 'class name',
    'value' => 'suggest2-form__button')));
$result = json_decode(curl_exec($ch), true);

$id = $result['value']['element-6066-11e4-a52e-4f735466cecf'];
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element/$id/click");
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/elements");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
    'using' => 'class name',
    'value' => 'needsclick')));
$search_result = json_decode(curl_exec($ch), true);
$search_count = count($search_result['value']);
if ($search_count < 10) {
    echo "Test failed. The search count is '" . $search_count . "'\n";
    }

foreach ($search_result['value'] as $element){
    $el_id = $element['element-6066-11e4-a52e-4f735466cecf'];
    curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId/element/$el_id/text");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $result = json_decode(curl_exec($ch), true);
    $el_value = strtolower($result['value']);
    if ($el_value != "selenium") {
        echo "Test failed. The value is '" . $el_value . "'\n";
    }
}
//удаление сессии
curl_setopt($ch, CURLOPT_URL, "$selenium_url/session/$sessionId");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_exec($ch);

//закрываем curl сессию
curl_close($ch);
