<?php

class DB {
    private const DB_PATH = __DIR__ . "/../pozzo.DB";
    private static $pdb = null;

    static function Init() {
        if (self::$pdb == null) {
            self::$pdb = new SQLite3(self::DB_PATH);
            self::$pdb->busyTimeout(2000);

            $prepCommand = "CREATE TABLE IF NOT EXISTS photos (";
            $prepCommand .= "id INTEGER PRIMARY KEY, ";
            $prepCommand .= "title TEXT, ";
            $prepCommand .= "hash TEXT, ";
            $prepCommand .= "width INTEGER, height INTEGER, ";
            $prepCommand .= "size INTEGER";
            $prepCommand .= ")";

            $statement = self::$pdb->prepare($prepCommand);
            $statement->execute();
        }
    }

    static function Cleanup() {
        self::$pdb->close();
        self::$pdb = null;
    }

    static function Reset() {
        unlink(self::DB_PATH);
        self::Init();
    }

    static function GetAllPhotos() {
        $statement = self::$pdb->prepare(
            "SELECT id, title, hash, width, height, size FROM photos",
        );
        $results = $statement->execute();

        $ret = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            array_push($ret, $row);
        }
        return $ret;
    }

    static function InsertPhoto($photoData) {
        $statement = self::$pdb->prepare(
            "INSERT INTO photos(title, hash, width, height, size) values(?,?,?,?,?)",
        );
        $statement->bindParam(1, $photoData["title"], SQLITE3_TEXT);
        $statement->bindParam(2, $photoData["hash"], SQLITE3_TEXT);
        $statement->bindParam(3, $photoData["width"], SQLITE3_INTEGER);
        $statement->bindParam(4, $photoData["height"], SQLITE3_INTEGER);
        $statement->bindParam(5, $photoData["size"], SQLITE3_INTEGER);

        $statement->execute();
        $photoData["id"] = self::$pdb->lastInsertRowID();
        return $photoData["id"];
    }
}
