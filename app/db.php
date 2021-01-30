<?php

require_once __DIR__ . '/util.php';

class DB {
    private const DB_PATH = __DIR__ . "/../pozzo.DB";
    private static $pdb = null;

    static function Init() {
        if (self::$pdb == null) {
            if (file_exists(self::DB_PATH)) {
                self::$pdb = new SQLite3(self::DB_PATH);
            }
            else {
                self::$pdb = new SQLite3(self::DB_PATH);
                self::_createDB();
            }
            self::$pdb->busyTimeout(2000);
        }
    }

    static private function _createDB() {
        $prepCommand = "CREATE TABLE IF NOT EXISTS config(";
        $prepCommand .= "key TEXT NOT NULL UNIQUE,";
        $prepCommand .= "value TEXT,";
        $prepCommand .= "type TEXT CHECK(type IN ('integer', 'string', 'float'))";
        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand = "CREATE TABLE IF NOT EXISTS photos (";
        $prepCommand .= "id INTEGER PRIMARY KEY, ";
        $prepCommand .= "title TEXT, ";
        $prepCommand .= "hash TEXT, ";
        $prepCommand .= "width INTEGER, height INTEGER, ";
        $prepCommand .= "size INTEGER";
        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand = "CREATE TABLE IF NOT EXISTS albums (";
        $prepCommand .= "id INTEGER PRIMARY KEY, ";
        $prepCommand .= "title TEXT, ";
        $prepCommand .= "description TEXT";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand =
            "INSERT INTO albums(title, description) VALUES('Unsorted', '')";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();


        self::SetConfig("created", 1, "integer");
    }

    static function Cleanup() {
        self::$pdb->close();
        self::$pdb = null;
    }

    static function SetConfig($key, $value, $type) {
        if (!stringInArray($type, ["integer", "string", "float"])) {
            return false;
        }
        $prepCommand = "INSERT INTO config(key, value, type) VALUES (?, ?, ?)";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->bindParam(1, $key, SQLITE3_TEXT);
        $statement->bindParam(2, $value, SQLITE3_TEXT);
        $statement->bindParam(3, $type, SQLITE3_TEXT);
        $statement->execute();
        return true;
    }

    static function GetConfig($key) {
        $prepCommand = "SELECT * FROM config WHERE (key = ?)";
        $statement = self::$pdb->prepare($prepCommand);
        if ($statement == false) {
            return false;
        }
        $statement->bindParam(1, $key, SQLITE3_TEXT);
        $result = $statement->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $result->finalize();
        if ($row == null) {
            return false;
        }

        switch ($row["type"]) {
            case 'integer':
                return (int)$row["value"];
                break;
            case 'float':
                return (float)$row["value"];
                break;
            case 'string':
                return (string)$row["value"];
                break;
        }

        return false;
    }

    static function Reset() {
        self::Cleanup();
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
        $results->finalize();
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
