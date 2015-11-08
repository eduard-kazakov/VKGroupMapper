<?php

set_time_limit(200000);
include "vkAPI.php";

// �������� ������ �� ������������
    if (isset ($_POST['range_count'])) {
        $range_count = $_POST['range_count'];
    } else {
        echo "�� ������� ���������� ������� ��� ���������� �����. <a href='../index.html'>���������</a>";
        return 0;
    };
    
    if (isset ($_POST['range_type'])) {
        $range_type = $_POST['range_type'];
    } else {
        echo "�� ������ ������ ���������� ����������. <a href='../index.html'>���������</a>";
        return 0;
    };
    
    if (isset ($_POST['jsfile'])) {
        $jsfile = $_POST['jsfile'];
    } else {
        echo "�� ������� �������� �����. <a href='../index.html'>���������</a>";
        return 0;
    };
    
    if (isset ($_POST['map_type'])) {
        $map_type = $_POST['map_type'];
    } else {
        echo "�� ������ ��� �����. <a href='../index.html'>���������</a>";
        return 0;
    };
    
    if ($range_count > 7 || $range_count < 3) {
        echo "������������ ����� �������. <a href='../index.html'>���������</a>";
        return 0;
    }
    
    
    $public_id = substr($jsfile,0,-7);
    if ($map_type == "cartogram") {
        header("Location: cartogram-map.php?data_source=../userfiles/$jsfile&public_id=$public_id&number_of_classes=$range_count&range_type=$range_type");
    } else if ($map_type == "quantity") {
        header("Location: quantity-map.php?data_source=../userfiles/$jsfile&public_id=$public_id&number_of_classes=$range_count&range_type=$range_type");
    } else {
        echo "����������� ��� �����. <a href='../index.html'>���������</a>";
        return 0;
    }
    
?>  