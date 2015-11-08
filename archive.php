<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
    <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>VK Group Mapper Alpha</title>
            
            <script type="text/javascript" src="jquery-1.11.3.min.js    "></script>
            
            <script type="text/javascript">
                function ShowLoading(e) {
                    var div = document.createElement('div');
                    var img = document.createElement('img');
                    img.src = 'loading.gif';
                    div.innerHTML = "<b>Пожалуйста, подождите</b><br>Обработка задачи...<br />";
                    div.style.cssText = 'margin: 0 auto; z-index: 5000; text-align: center;';
                    div.appendChild(img);
                    document.body.appendChild(div);
                    return true;
                    // These 2 lines cancel form submission, so only use if needed.
                    //window.event.cancelBubble = true;
                    //e.stopPropagation();
                }
            </script>
            
    </head>
    <body>
        <div style ="position:absolute; top:100px; text-align: center; width: 100%">
        <a href = "index.html">На главную</a> | <a href = "help.html">Помощь</a><br><br>
        VK GROUP MAPPER (Alpha v.0.001) - Архив карт<br><br><br><br>
        
        <table align="center" border="0">
            <tr>
                <td style="width: 200px;">
                    Тип карты
                </td>
                <td style="width: 200px;">
                    Способ выбора диапазонов
                </td>
                <td style="width: 200px;">
                    Количество диапазонов
                </td>
            </tr>
        </table>
        <form method="post" action="php\vkGroupMapperFromArhive.php" onsubmit="ShowLoading()">
            <select type="select" name="map_type" style="width: 200px;">
                <option value="cartogram">Картограмма</option>
                <option value="quantity">Количественный фон</option>
            </select>
            <select type="select" name="range_type" style="width: 200px;">
                <option value="1">Равные диапазоны</option>
                <option value="0">Квантили</option>
            </select>
            <select type="select" name="range_count" style="width: 200px;">
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select><br><br>
            <?php
                include "php/vkAPI.php";
                
                $vkDB = new vkDatabaseManager ("localhost",5432,"postgres","spsupostgis","vkGroupMapper");
                $vkDB->connect();
                $archiveMaps = $vkDB->getArchiveMaps();
                
                echo '<select type="select" name="jsfile" style="width: 600px;">';
                foreach ($archiveMaps as $map) {
                    echo "<option value='$map->jsFile'>$map->public_id ($map->timeStamp)</option>";
                }
                echo "</select>";
                
            ?>
            <br><br>
            <input type="submit" value="Показать">
        </form>
        
        
        
        
        <br><br><br>
        By Eduard Kazakov, Gennady Akimov, 2015.
        </div>
    </body>
</html>