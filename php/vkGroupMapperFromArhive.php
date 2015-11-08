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
    
    if (isset ($_POST['jsfile'])) {
        $jsfile = $_POST['jsfile'];
    } else {
        echo "Не выбрана архивная карта. <a href='../index.html'>Вернуться</a>";
        return 0;
    };
    
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
    
    
    $public_id = substr($jsfile,0,-7);
    if ($map_type == "cartogram") {
        header("Location: cartogram-map.php?data_source=../userfiles/$jsfile&public_id=$public_id&number_of_classes=$range_count&range_type=$range_type");
    } else if ($map_type == "quantity") {
        header("Location: quantity-map.php?data_source=../userfiles/$jsfile&public_id=$public_id&number_of_classes=$range_count&range_type=$range_type");
    } else {
        echo "Неизвестный тип карты. <a href='../index.html'>Вернуться</a>";
        return 0;
    }
    
?>  