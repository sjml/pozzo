<?php

// You can either see this file as a mess of copypasta or as a triumph of
//   hand-crafted PHP/SQL bindings without needing to use an ORM.
// ¿Por qué no los dos?

use Ausi\SlugGenerator\SlugGenerator;

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/image.php";

class DB {
    private const DB_PATH = __DIR__ . "/../pozzo.DB";
    private static $pdb = null;

    static function Init() {
        if (self::$pdb == null) {
            $creationNeeded = false;
            if (!file_exists(self::DB_PATH)) {
                $creationNeeded = true;
            }

            self::$pdb = new SQLite3(self::DB_PATH);
            self::$pdb->busyTimeout(2000);
            self::$pdb->enableExceptions(true);
            self::$pdb->query("PRAGMA foreign_keys = ON;");
            if ($creationNeeded) {
                self::_createDB();
            }
        }
    }

    // @codeCoverageIgnoreStart
    // Gets automatically called at script exit/die/fail, so coverage
    //   detection can't hook in to see it happen.
    static function Cleanup() {
        if (self::$pdb != null) {
            self::$pdb->close();
            self::$pdb = null;
        }
    }
    // @codeCoverageIgnoreEnd

    private static function _createDB() {
        $prepCommand = "CREATE TABLE IF NOT EXISTS users(";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", name TEXT NOT NULL UNIQUE";
        $prepCommand .= ", password TEXT";
        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand = "CREATE TABLE IF NOT EXISTS config(";
        $prepCommand .= "key TEXT NOT NULL UNIQUE";
        $prepCommand .= ", value TEXT";
        $prepCommand .=
            ", type TEXT CHECK(type IN ('integer', 'string', 'float'))";
        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand = "CREATE TABLE IF NOT EXISTS photos (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", originalFilename TEXT";
        $prepCommand .= ", title TEXT";
        $prepCommand .= ", hash TEXT";
        $prepCommand .= ", uniq TEXT";
        $prepCommand .= ", width INTEGER, height INTEGER";
        $prepCommand .= ", aspect FLOAT";
        $prepCommand .= ", uploadTimeStamp DATETIME";
        $prepCommand .= ", uploadedBy INTEGER";
        $prepCommand .= ", size INTEGER";
        $prepCommand .= ", latitude FLOAT";
        $prepCommand .= ", longitude FLOAT";
        $prepCommand .= ", FOREIGN KEY(uploadedBy) REFERENCES users(id)";

        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand =
            "CREATE TABLE IF NOT EXISTS photoMeta (id INTEGER PRIMARY KEY, ";
        $fieldDescs = [];
        foreach (photoExifFields as $meta => $datums) {
            foreach ($datums as $field) {
                $dbFieldName = $meta . "_" . $field . " ";
                if (strpos($field, "DateTime") !== false) {
                    array_push($fieldDescs, $dbFieldName . " " . "DATETIME");
                } else {
                    array_push($fieldDescs, $dbFieldName . " " . "TEXT");
                }
            }
        }

        $prepCommand .= implode(", ", $fieldDescs) . ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand = "CREATE TABLE IF NOT EXISTS photoPreviews (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", tinyJPEG TEXT";
        $prepCommand .= ", tinyWebP TEXT";
        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $prepCommand = "CREATE TABLE IF NOT EXISTS albums (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", title TEXT UNIQUE";
        $prepCommand .= ", slug TEXT";
        $prepCommand .= ", isPrivate BOOLEAN";
        $prepCommand .= ", description TEXT";
        $prepCommand .= ", ordering INTEGER";
        $prepCommand .= ", coverPhoto INTEGER";
        $prepCommand .= ", showMap BOOLEAN";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        $unsortedIdx = self::CreateAlbum("[unsorted]");
        self::SetConfig("unsorted_album_index", $unsortedIdx, "integer");

        $prepCommand = "CREATE TABLE IF NOT EXISTS photos_albums (";
        $prepCommand .= "photo_id INTEGER NOT NULL";
        $prepCommand .= ", album_id INTEGER NOT NULL";
        $prepCommand .= ", ordering INTEGER";
        $prepCommand .=
            ", CONSTRAINT PK_photo_album PRIMARY KEY (photo_id, album_id)";
        $prepCommand .= ", FOREIGN KEY(photo_id) REFERENCES photos(id)";
        $prepCommand .= ", FOREIGN KEY(album_id) REFERENCES albums(id)";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();

        self::SetConfig("app_key", Auth::GenerateKey(), "string");
        self::SetConfig("jwt_expiration", 60 * 60 * 24, "integer");

        self::SetConfig("created", 1, "integer");
    }

    static function CreateUser($user, $pw) {
        $pw = Auth::HashPassword($pw);
        $prepCommand = "INSERT INTO users (name, password) VALUES (?, ?)";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->bindParam(1, $user, SQLITE3_TEXT);
        $statement->bindParam(2, $pw, SQLITE3_TEXT);
        try {
            $result = $statement->execute();
        // @codeCoverageIgnoreStart
        // user creation is intentionally not exposed to the API, so
        //    testing this would be annoying and not prove much.
        // this would only fail if (a) the database was failing for
        //    other reasons caught in other tests or (b) the name already
        //    exists.
        } catch (\Throwable $th) {
            return false;
        }
        // @codeCoverageIgnoreEnd
        return ["id" => self::$pdb->lastInsertRowID(), "name" => $user];
    }

    static function GetUser($username, $includePWH = false) {
        if ($includePWH) {
            $query = "SELECT id, name, password FROM users WHERE name = ?";
        } else {
            $query = "SELECT id, name FROM users WHERE name = ?";
        }
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $username, SQLITE3_TEXT);
        $results = $statement->execute();
        if ($results == false) {
            return null;
        }
        return $results->fetchArray(SQLITE3_ASSOC);
    }

    static function SetConfig($key, $value, $type) {
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
        $statement->bindParam(1, $key, SQLITE3_TEXT);
        $result = $statement->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $result->finalize();
        if ($row == null) {
            return false;
        }

        switch ($row["type"]) {
            case "integer":
                return (int) $row["value"];
            case "float":
                return (float) $row["value"];
            case "string":
                return (string) $row["value"];
        }

        return false;
    }

    static function InsertPhoto(
        $photoData,
        $originalFilename,
        $albumID,
        $order
    ) {
        $statement = self::$pdb->prepare(
            "INSERT INTO photos (title, originalFilename, hash, uniq, width, height, aspect, size, uploadTimeStamp, uploadedBy, latitude, longitude) VALUES (?,?,?,?,?,?,?,?,datetime('now'),?,?,?)",
        );
        $statement->bindParam(1, $photoData["title"], SQLITE3_TEXT);
        $statement->bindParam(2, $originalFilename, SQLITE3_TEXT);
        $statement->bindParam(3, $photoData["hash"], SQLITE3_TEXT);
        $statement->bindParam(4, $photoData["uniq"], SQLITE3_TEXT);
        $statement->bindParam(5, $photoData["width"], SQLITE3_INTEGER);
        $statement->bindParam(6, $photoData["height"], SQLITE3_INTEGER);
        $statement->bindParam(7, $photoData["aspect"], SQLITE3_FLOAT);
        $statement->bindParam(8, $photoData["size"], SQLITE3_INTEGER);
        $statement->bindParam(9, $photoData["uploadedBy"], SQLITE3_INTEGER);
        $statement->bindParam(10, $photoData["latitude"], SQLITE3_FLOAT);
        $statement->bindParam(11, $photoData["longitude"], SQLITE3_FLOAT);

        $statement->execute();
        $photoData["id"] = self::$pdb->lastInsertRowID();

        $vals = [];
        $fieldList = [];
        $prepCommand = "INSERT INTO photoMeta (id";
        foreach (photoExifFields as $meta => $datums) {
            foreach ($datums as $field) {
                if ($photoData[$meta . "_" . $field] != null) {
                    array_push($fieldList, $meta . "_" . $field);
                    if (strpos($field, "DateTime") !== false) {
                        $date = \DateTime::createFromFormat(
                            "Y:m:d H:i:s",
                            $photoData[$meta . "_" . $field],
                        );

                        array_push($vals, [
                            $date->format("U"),
                            SQLITE3_INTEGER,
                        ]);
                    } else {
                        array_push($vals, [
                            $photoData[$meta . "_" . $field],
                            SQLITE3_TEXT,
                        ]);
                    }
                }
            }
        }

        if (count($fieldList) > 0) {
            $prepCommand .= ", " . implode(", ", $fieldList);
        }
        $prepCommand .= ") ";
        $prepCommand .= "VALUES (?" . str_repeat(", ?", count($vals)) . ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->bindParam(1, $photoData["id"]);
        foreach ($vals as $i => $value) {
            $statement->bindParam($i + 2, $value[0], $value[1]);
        }
        $statement->execute();

        $statement = self::$pdb->prepare(
            "INSERT INTO photoPreviews(id, tinyJPEG) VALUES (?, ?)",
        );
        $statement->bindParam(1, $photoData["id"], SQLITE3_INTEGER);
        $statement->bindParam(2, $photoData["tinyJPEG"], SQLITE3_TEXT);
        $statement->execute();

        self::AddPhotoToAlbum($photoData["id"], $albumID, $order);

        return $photoData["id"];
    }

    static function GetPhoto($id, $getPreview = false) {
        if (!$getPreview) {
            $query = "SELECT * FROM photos WHERE id = ?";
        } else {
            $query =
                "SELECT * FROM photos INNER JOIN photoPreviews ON photos.id = photoPreviews.id WHERE photos.id = ?;";
        }
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $id, SQLITE3_INTEGER);
        $results = $statement->execute();
        if ($results == false) {
            return null;
        }
        return $results->fetchArray(SQLITE3_ASSOC);
    }

    static function GetMeta($id) {
        $query = "SELECT * FROM photoMeta WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $id, SQLITE3_INTEGER);
        $results = $statement->execute();
        if ($results == false) {
            return null;
        }
        return $results->fetchArray(SQLITE3_ASSOC);
    }

    static function DeletePhoto($id) {
        $photoData = self::GetPhoto($id);
        if ($photoData == null) {
            return -1;
        }

        $query = "DELETE FROM photos_albums WHERE photo_id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();

        $query = "UPDATE albums SET coverPhoto = -1 WHERE coverPhoto = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();

        $query = "DELETE FROM photoPreviews WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();

        $query = "DELETE FROM photoMeta WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();

        $query = "DELETE FROM photos WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();
        if (self::$pdb->changes() == 0) {
            return -2;
        }

        return $photoData;
    }

    static function GetAlbumList($includePrivate = false) {
        $prepCommand = "SELECT albums.*";
        $prepCommand .= ", COALESCE(photos.hash, null) as coverHash";
        $prepCommand .= ", COALESCE(photos.uniq, null) as coverUniq";
        $prepCommand .= ", COALESCE(photos.aspect, null) as coverAspect";
        $prepCommand .= " FROM albums";
        $prepCommand .=
            " LEFT OUTER JOIN photos ON photos.id = albums.coverPhoto";
        if (!$includePrivate) {
            $prepCommand .= " WHERE isPrivate != 1";
        }
        $prepCommand .= " ORDER BY ordering ASC, id ASC";
        $statement = self::$pdb->prepare($prepCommand);
        $results = $statement->execute();

        $ret = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            array_push($ret, $row);
        }
        $results->finalize();
        return $ret;
    }

    static function ReorderAlbumList($newOrdering) {
        // see note on ReorderAlbum function; it also applies here...
        $prepCommand = "UPDATE albums SET ordering = ? WHERE id = ?";
        $statement = self::$pdb->prepare($prepCommand);
        foreach ($newOrdering as $i => $aid) {
            $orderIdx = $i + 1; // PHP gets cranky passing arithmetic results directly :-/
            $statement->bindParam(1, $orderIdx, SQLITE3_INTEGER);
            $statement->bindParam(2, $aid, SQLITE3_INTEGER);
            $statement->execute();
            $statement->reset();
        }
        return true;
    }

    static function CreateAlbum($title, $isPrivate = false) {
        if (is_numeric($title)) {
            return -2;
        }

        $sg = new SlugGenerator();
        $slug = $sg->generate($title);

        $prepCommand =
            "INSERT INTO albums(title, slug, description, isPrivate, showMap, coverPhoto) VALUES(?, ?, '', ?, 0, -1)";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->bindParam(1, $title, SQLITE3_TEXT);
        $statement->bindParam(2, $slug, SQLITE3_TEXT);
        $statement->bindParam(3, $isPrivate, SQLITE3_INTEGER);
        try {
            $result = $statement->execute();
            if (!$result) {
                return -1;
            }
        } catch (\Throwable $th) {
            return -1;
        }
        $id = self::$pdb->lastInsertRowID();

        self::$pdb->exec("BEGIN IMMEDIATE TRANSACTION;");
        $order = self::$pdb->querySingle("SELECT MAX(ordering) FROM albums;");
        if ($order == null) {
            $order = 0;
        }
        $order += 1;
        $statement = self::$pdb->prepare(
            "UPDATE albums SET ordering = ? WHERE id = ?",
        );
        $statement->bindParam(1, $order, SQLITE3_INTEGER);
        $statement->bindParam(2, $id, SQLITE3_INTEGER);
        $statement->execute();
        self::$pdb->exec("COMMIT TRANSACTION;");

        return $id;
    }

    static function DeleteAlbum($id) {
        $albumData = self::FindAlbum($id, false);
        if ($albumData == null) {
            return -1;
        }

        $query = "DELETE FROM photos_albums WHERE album_id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $albumData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();

        $query = "DELETE FROM albums WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $albumData["id"], SQLITE3_INTEGER);
        $results = $statement->execute();

        return $albumData;
    }

    static function UpdateAlbumMeta(
        $id,
        $title,
        $description,
        $isPrivate,
        $showMap,
        $coverPhoto
    ) {
        $query =
            "UPDATE albums SET title = ?, description = ?, isPrivate = ?, showMap = ?, coverPhoto = ? WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $title, SQLITE3_TEXT);
        $statement->bindParam(2, $description, SQLITE3_TEXT);
        $statement->bindParam(3, $isPrivate, SQLITE3_INTEGER);
        $statement->bindParam(4, $showMap, SQLITE3_INTEGER);
        $statement->bindParam(5, $coverPhoto, SQLITE3_INTEGER);
        $statement->bindParam(6, $id, SQLITE3_INTEGER);
        $result = $statement->execute();
        if (!$result) {
            return -1;
        }
        return 1;
    }

    static function AddPhotoToAlbum($photoID, $albumID, $order) {
        if ($albumID == null) {
            $albumID = self::GetConfig("unsorted_album_index");
        }

        $statement = self::$pdb->prepare(
            "SELECT ordering FROM photos_albums WHERE album_id = ? ORDER BY ordering ASC",
        );
        $statement->bindParam(1, $albumID);
        $results = $statement->execute();
        $indices = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            array_push($indices, intval($row["ordering"]));
        }
        if ($order != null) {
            while (in_array($order, $indices)) {
                $order += 1;
            }
        } else {
            if (count($indices) > 0) {
                $order = max($indices) + 1;
            } else {
                $order = 1;
            }
        }

        $statement = self::$pdb->prepare(
            "INSERT INTO photos_albums (photo_id, album_id, ordering) VALUES(?, ?, ?)",
        );
        $statement->bindParam(1, $photoID, SQLITE3_INTEGER);
        $statement->bindParam(2, $albumID, SQLITE3_INTEGER);
        $statement->bindParam(3, $order, SQLITE3_INTEGER);
        try {
            $result = $statement->execute();
            if ($result == false) {
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }

    static function RemovePhotoFromAlbum($photoID, $albumID) {
        $query = "SELECT coverPhoto FROM albums WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $albumID, SQLITE3_INTEGER);
        $result = $statement->execute();
        $coverPhotoID = $result->fetchArray(SQLITE3_ASSOC)["coverPhoto"];
        if ($coverPhotoID == $photoID) {
            $query = "UPDATE albums SET coverPhoto = -1 WHERE id = ?";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $albumID, SQLITE3_INTEGER);
            $statement->execute();
        }

        $query =
            "DELETE FROM photos_albums WHERE (photo_id = ? AND album_id = ?)";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoID, SQLITE3_INTEGER);
        $statement->bindParam(2, $albumID, SQLITE3_INTEGER);
        $results = $statement->execute();
        $changes = self::$pdb->changes();
        return $changes;
    }

    static function GetPhotosInAlbum($albumID, $getPreviews = false) {
        $ret = [];
        $query = "SELECT * FROM photos_albums ";
        $query .= "JOIN photos ON photos.id = photos_albums.photo_id ";
        if ($getPreviews) {
            $query .=
                "INNER JOIN photoPreviews ON photos.id = photoPreviews.id ";
        }
        $query .= "WHERE photos_albums.album_id = ? ORDER BY ordering";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $albumID, SQLITE3_INTEGER);
        $results = $statement->execute();
        if ($results == false) {
            return $ret;
        }
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            unset($row["photo_id"]);
            unset($row["album_id"]);
            // intentionally leaving the ordering
            array_push($ret, $row);
        }
        return $ret;
    }

    static function ReorderAlbum($albumID, $newOrdering) {
        // there is almost certainly some clever SQL I could write to do this all
        //   in one statement...
        $prepCommand =
            "UPDATE photos_albums SET ordering = ? WHERE photo_id = ? AND album_id = ?";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->bindParam(3, $albumID, SQLITE3_INTEGER);
        foreach ($newOrdering as $i => $pid) {
            $orderIdx = $i + 1; // PHP gets cranky passing arithmetic results directly :-/
            $statement->bindParam(1, $orderIdx, SQLITE3_INTEGER);
            $statement->bindParam(2, $pid, SQLITE3_INTEGER);
            $statement->execute();
            $statement->reset();
        }
        return true;
    }

    static function FindAlbum(
        $identifier,
        $includePhotos = true,
        $previews = false
    ) {
        if (is_numeric($identifier)) {
            $query = "SELECT * FROM albums WHERE id = ?";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $identifier, SQLITE3_INTEGER);
        } else {
            $query = "SELECT * FROM albums WHERE slug = ?";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $identifier, SQLITE3_TEXT);
        }
        $results = $statement->execute();
        if ($results == false) {
            return $false;
        }
        $albumData = $results->fetchArray(SQLITE3_ASSOC);

        if (!isset($albumData) || $albumData == false) {
            return false;
        }

        if ($albumData["isPrivate"] && $_REQUEST["POZZO_AUTH"] <= 0) {
            return false;
        }

        if ($includePhotos) {
            $photos = self::GetPhotosInAlbum($albumData["id"], $previews);
            $albumData["photos"] = $photos;
        }

        return $albumData;
    }
}
