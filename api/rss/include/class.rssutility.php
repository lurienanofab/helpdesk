<?php
class RssUtility{
    static function Get($key) {
        return (isset($_GET[$key])) ? $_GET[$key] : "";
    }

    static function Post($key) {
        return (isset($_POST[$key])) ? $_POST[$key] : "";
    }

    static function ExecuteQuery($sql) {
        return db_query($sql);
    }
}
?>
