<?php
include "vkDatabaseManager.php";

class vkAPI {

    private $token;

    public function __construct($token) {
        $this->token= $token;
    }
    
    public function checkGroupExistance ($group_id) {
        $existance = json_decode(file_get_contents("https://api.vk.com/method/groups.getMembers?group_id=$group_id"),true);
        //var_dump($existance);
        if (isset ($existance['error'])) {
            return 0;
        } else {
            return 1;
        }
    }

    public function getGroupMembers ($group_id) {
    
        header('Content-type: application/json');
        $page = 0;
        $limit = 1000;
        $users = array();
        do {
            $offset = $page * $limit;
            //Получаем список пользователей
            $members = json_decode(file_get_contents("https://api.vk.com/method/groups.getMembers?group_id=$group_id&v=5.16&offset=$offset&count=$limit&fields=city"),true);
            
        
            //Спим
            sleep(1);
        
            foreach($members['response']['items'] as $user ) {
                if (isset ($user["city"]["title"])) {
                    $users []= $user; // добавляем юзера к юзерам
                }
            }
            //Увеличиваем страницу
            $page++;
        } while($members['response']['count'] > $offset + $limit );
        
        //var_dump($users);
        
        return $users;
    }
    
    function returnCitiesListFromUsersList ($users) {
        $cities = array ();
        foreach ($users as $user) {
            $cities[] = $user["city"]["title"];
        }
        return $cities;
    }

}
set_time_limit(200000);
//$vkDB = new vkDatabaseManager ("localhost",5432,"postgres","spsupostgis","vkGroupMapper");
//$vkDB->connect();
//$vk = new vkAPI ("token");
//$users = $vk->getGroupMembers ("openlektorium");
//$cities = $vk->returnCitiesListFromUsersList($users);
//$vkDB->createTableOfPublicCities("openlektorium",$cities);

?>