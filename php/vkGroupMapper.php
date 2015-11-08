<?php

set_time_limit(200000);
include "vkAPI.php";

// Получаем данные от пользователя
    if (isset ($_POST['range_count'])) {
        $range_count = $_POST['range_count'];
    } else {
        echo "Не указано количество классов для построения карты. <a href='../index.html'>Вернуться</a>";
        return 0;
    };
    
    if (isset ($_POST['range_type'])) {
        $range_type = $_POST['range_type'];
    } else {
        echo "Не указан способ построения диапазонов. <a href='../index.html'>Вернуться</a>";
        return 0;
    };
    
    if (isset ($_POST['public_id'])) {
        $public_id = $_POST['public_id'];
    } else {
        echo "Не указан ID группы. <a href='../index.html'>Вернуться</a>";
        return 0;
    };
    
    //if (ctype_digit(substr($public_id,1,1))) {
    //    echo "В текущей реализации не поддерживаются числовые ID групп и публичных страниц. <a href='../index.html'>Вернуться</a>";
    //    return 0;
    //}
    
    if (isset ($_POST['map_type'])) {
        $map_type = $_POST['map_type'];
    } else {
        echo "Не выбран тип карты. <a href='../index.html'>Вернуться</a>";
        return 0;
    };
    

    
    if ($range_count > 7 || $range_count < 3) {
        echo "Некорректное число классов. <a href='../index.html'>Вернуться</a>";
        return 0;
    }
    
    $vk = new vkAPI ("token");
    if ($vk->checkGroupExistance($public_id) == 0) {
        echo "Группы с таким ID не существует. <a href='../index.html'>Вернуться</a>";
        return 0;
    }
    
    
    // Начинаем работать
    // Здесь скармливаем айди паблика Гениному модулю, после работы которого должна быть создана табличка в БД
    
    $vkDB = new vkDatabaseManager ("localhost",5432,"postgres","spsupostgis","vkGroupMapper");
    $vkDB->connect();
    $users = $vk->getGroupMembers ($public_id);
    $cities = $vk->returnCitiesListFromUsersList($users);
    
    $salt = $vkDB->generateRandomString(4);
    $tableName = "g" . $public_id . $salt;
    
    $vkDB->createTableOfPublicCities($tableName,$cities);
    
    // ...
    
    // Теперь геокодируем всё, чего не было в табличке geocoding
    
    $citiesForGeoCode = $vkDB->getUncodedCities($tableName);
    $vkDB->geocodeCitiesAndAddToTable($citiesForGeoCode);
    $geoObjects = $vkDB->getGeoObjectsArrayFromTable($tableName);
    
    
    // Генерируем файл для фронтенда
    
    $fileBase = "C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/userfiles/" . $public_id . $salt;
    
    $generator = new geoJsonGenerator ($fileBase . ".geojson",$geoObjects);
    $generator->generateGeoJson();
    $generator->generateGeoJsonStatedWithPolygons($fileBase . ".json");
    $generator->geoJsonToJS ($fileBase . ".json", $fileBase . ".js");
    
    $jsName = $public_id . $salt . ".js";
    
    $timeStamp = date('Y/m/d H:i');
    
    $vkDB->addMapToArchive ($public_id,$jsName,$timeStamp);
    $vkDB->closeConnection();
    if ($map_type == "cartogram") {
        header("Location: cartogram-map.php?data_source=../userfiles/$jsName&public_id=$public_id&number_of_classes=$range_count&range_type=$range_type");
    } else if ($map_type == "quantity") {
        header("Location: quantity-map.php?data_source=../userfiles/$jsName&public_id=$public_id&number_of_classes=$range_count&range_type=$range_type");
    } else {
        echo "Неизвестный тип карты. <a href='../index.html'>Вернуться</a>";
        return 0;
    }
    
?>  