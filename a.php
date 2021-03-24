<?php
class DB
{
    public static $dsn = 'mysql:host=localhost;dbname=universitet;charset=utf8';
    public static $user = 'mar';
    public static $pass = '12345678';
    public static $dbh = null;
    public static $sth = null;
    public static $question = '';

    public static function dbh()
    {
        $options = [PDO::ATTR_ERRMODE            => PDO::ERRMODE_WARNING,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
        ];
        self::$dbh = new PDO(self::$dsn, self::$user, self::$pass, $options );
        return self::$dbh;
    }

    public function setTree($question, $per = array())
    {
        self::$sth = self::dbh()->prepare($question);
        self::$sth->execute((array)$per);
        return self::$sth->fetchAll();
    }

    function VTree(array &$elements, $parent = 0){

        $branch = array();

        foreach ($elements as $element) {

            if ($element['parent'] == $parent) {
                $children = DB::VTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$element['id']] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    function VTreeDelete(array &$elements, $parent = 0, $delete_id){

        foreach ($elements as $element) {

            if ($delete_id == $element['parent']) {
                $children = DB::VTreeDelete($elements, $element['id'], $element['id']);
                if ($children == false) {
                    DB::setTree("DELETE FROM `tree` WHERE id = ?", $element['id']);
                }

            } elseif ($element['parent'] == $parent){
                DB::VTreeDelete($elements, $element['id'], $delete_id );
                if ($delete_id == $element['id']) {
                    DB::setTree("DELETE FROM `tree` WHERE id = ?", $delete_id);
                }
            }
        }
    }
}
   /* $result = array();
    $array = DB::setTree("SELECT * FROM `tree`");
    $result = DB::VTree($array,0);//объемное дерево вывод
    echo json_encode($result);

    $items = DB::setTree("SELECT * FROM `tree` WHERE `id` >  ?", 0);
    echo json_encode($items);// вывод всего плоского дерева

    echo ' <br/>';
    $value = DB::setTree("SELECT `name` FROM `tree` WHERE `id` = 20");
    echo json_encode($value);//Вывод отдельного узла дерева

    echo ' <br/>';
    DB::setTree("UPDATE tree SET name = 'FKBTEK' WHERE id = 2");//изменение узла дерева
    $value2 = DB::setTree("SELECT `name` FROM `tree` WHERE `id` = 2");
    echo json_encode($value2);

    $result = array();
    $array = DB::setTree("SELECT * FROM `tree`");
    $result = DB::VTreeDelete($array,0,26); // объемное дерево удаление узла с потомками
    $array = DB::setTree("SELECT * FROM `tree`");
    $result = DB::VTree($array,0);
    echo json_encode($result);

    $result = array();
    $array = DB::setTree("SELECT * FROM `tree`");
    $result = DB::VTree($array,24); // вывод ветки по id родителя
    echo json_encode($result);

    $value_manage = 'Koshevin';
    DB::setTree("DELETE FROM `head` WHERE manage = ?", $value_manage);//удаление ответственного

    $value_id = 11;
    $value = DB::setTree("SELECT * FROM `head` WHERE `id` = ?", $value_id );//вывод всех ответственных за подразделение
    echo json_encode($value);

    $value_id = 3;
    DB::setTree("UPDATE `head` SET manage = 'Kydrav' WHERE id = ?", $value_id);//изменение ответственного
    $value = DB::setTree("SELECT `manage` FROM `head` WHERE `id` = ?", $value_id);
    echo json_encode($value);*/
