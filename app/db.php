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
        $prepCommand .= ", uploadTimeStamp DATETIME";
        $prepCommand .= ", uploadedBy INTEGER";
        $prepCommand .= ", originalFilename TEXT";
        $prepCommand .= ", size INTEGER";
        $prepCommand .= ", width INTEGER, height INTEGER";
        $prepCommand .= ", title TEXT";
        $prepCommand .= ", hash TEXT";
        $prepCommand .= ", uniq TEXT";
        $prepCommand .= ", blurHash TEXT";
        $prepCommand .= ", aspect FLOAT";
        $prepCommand .= ", isVideo BOOLEAN DEFAULT 0";

        $prepCommand .= ", make TEXT";
        $prepCommand .= ", model TEXT";
        $prepCommand .= ", lens TEXT";
        $prepCommand .= ", mime TEXT";
        $prepCommand .= ", creationDate DATETIME";
        $prepCommand .= ", tags TEXT";
        $prepCommand .= ", subjectArea TEXT";

        // set these as text in the hopes that the frontend wouldn't have to math,
        //    but alas EXIF data is not consistent in how it reports these.
        //    leaving as text because SQLite doesn't really care anyway and the
        //    frontend can just deal with what it gets.
        $prepCommand .= ", aperture TEXT";
        $prepCommand .= ", iso TEXT";
        $prepCommand .= ", shutterSpeed TEXT";

        $prepCommand .= ", gpsLat FLOAT";
        $prepCommand .= ", gpsLon FLOAT";
        $prepCommand .= ", gpsAlt FLOAT";

        $prepCommand .= ", FOREIGN KEY(uploadedBy) REFERENCES users(id)";

        $prepCommand .= ")";
        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();


        $prepCommand = "CREATE TABLE IF NOT EXISTS tags (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", tag TEXT UNIQUE";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();


        $prepCommand = "CREATE TABLE IF NOT EXISTS photos_tags (";
        $prepCommand .= "photo_id INTEGER NOT NULL";
        $prepCommand .= ", tag_id INTEGER NOT NULL";
        $prepCommand .=
            ", CONSTRAINT PK_photo_tag PRIMARY KEY (photo_id, tag_id)";
        $prepCommand .= ", FOREIGN KEY(photo_id) REFERENCES photos(id)";
        $prepCommand .= ", FOREIGN KEY(tag_id) REFERENCES tags(id)";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();


        $prepCommand = "CREATE TABLE IF NOT EXISTS albums (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", title TEXT UNIQUE";
        $prepCommand .= ", slug TEXT UNIQUE";
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


        $prepCommand = "CREATE TABLE IF NOT EXISTS interstitials (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", album_id INTEGER";
        $prepCommand .= ", before INTEGER";
        $prepCommand .= ", text TEXT";
        $prepCommand .= ", FOREIGN KEY(album_id) REFERENCES albums(id)";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();


        $prepCommand = "CREATE TABLE IF NOT EXISTS collections (";
        $prepCommand .= "id INTEGER PRIMARY KEY";
        $prepCommand .= ", name TEXT";
        $prepCommand .= ", description TEXT";
        $prepCommand .= ", coverPhoto INTEGER";
        $prepCommand .= ")";

        $statement = self::$pdb->prepare($prepCommand);
        $statement->execute();


        $prepCommand = "CREATE TABLE IF NOT EXISTS albums_collections (";
        $prepCommand .= "album_id INTEGER NOT NULL";
        $prepCommand .= ", collection_id INTEGER NOT NULL";
        $prepCommand .= ", ordering INTEGER";
        $prepCommand .=
            ", CONSTRAINT PK_photo_album PRIMARY KEY (album_id, collection_id)";
        $prepCommand .= ", FOREIGN KEY(album_id) REFERENCES albums(id)";
        $prepCommand .= ", FOREIGN KEY(collection_id) REFERENCES collections(id)";
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
        }
        // @codeCoverageIgnoreStart
        // user creation is intentionally not exposed to the API, so
        //    testing this would be annoying and not prove much.
        // this would only fail if (a) the database was failing for
        //    other reasons caught in other tests or (b) the name already
        //    exists.
        catch (\Throwable $th) {
            return false;
        }
        // @codeCoverageIgnoreEnd
        return ["id" => self::$pdb->lastInsertRowID(), "name" => $user];
    }

    static function GetUser($username, $includePWH = false) {
        if ($includePWH) {
            $query = "SELECT id, name, password FROM users WHERE name = ?";
        }
        else {
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
            "INSERT INTO photos (
                uploadTimeStamp, uploadedBy, originalFilename, isVideo, size,
                width, height, title, hash, uniq, blurHash, aspect,
                make, model, lens, mime, creationDate, tags, subjectArea,
                aperture, iso, shutterSpeed, gpsLat, gpsLon, gpsAlt
            ) VALUES (
                datetime('now'), :uploadedBy, :originalFilename, :isVideo, :size,
                :width, :height, :title, :hash, :uniq, :blurHash, :aspect,
                :make, :model, :lens, :mime, :creationDate, :tags, :subjectArea,
                :aperture, :iso, :shutterSpeed, :gpsLat, :gpsLon, :gpsAlt
            )",
        );

        $statement->bindParam(
            ":uploadedBy",
            $photoData["uploadedBy"],
            SQLITE3_INTEGER,
        );
        $statement->bindParam(
            ":originalFilename",
            $originalFilename,
            SQLITE3_TEXT,
        );
        $statement->bindParam(":size", $photoData["size"], SQLITE3_INTEGER);
        $statement->bindParam(":isVideo", $photoData["isVideo"], SQLITE3_INTEGER);
        $statement->bindParam(":width", $photoData["width"], SQLITE3_INTEGER);
        $statement->bindParam(":height", $photoData["height"], SQLITE3_INTEGER);
        $statement->bindParam(":title", $photoData["title"], SQLITE3_TEXT);
        $statement->bindParam(":hash", $photoData["hash"], SQLITE3_TEXT);
        $statement->bindParam(":uniq", $photoData["uniq"], SQLITE3_TEXT);
        $statement->bindParam(":blurHash", $photoData["blurHash"], SQLITE3_TEXT);
        $statement->bindParam(":aspect", $photoData["aspect"], SQLITE3_FLOAT);
        $statement->bindParam(":make", $photoData["make"], SQLITE3_TEXT);
        $statement->bindParam(":model", $photoData["model"], SQLITE3_TEXT);
        $statement->bindParam(":lens", $photoData["lens"], SQLITE3_TEXT);
        $statement->bindParam(":mime", $photoData["mime"], SQLITE3_TEXT);
        $statement->bindParam(":creationDate", $photoData["creationDate"], SQLITE3_INTEGER);
        $statement->bindParam(":tags", $photoData["tags"], SQLITE3_TEXT);
        $statement->bindParam(":subjectArea", $photoData["subjectArea"], SQLITE3_TEXT);
        $statement->bindParam(":aperture", $photoData["aperture"], SQLITE3_TEXT);
        $statement->bindParam(":iso", $photoData["iso"], SQLITE3_TEXT);
        $statement->bindParam(":shutterSpeed", $photoData["shutterSpeed"], SQLITE3_TEXT);
        $statement->bindParam(":gpsLat", $photoData["gpsLat"], SQLITE3_FLOAT);
        $statement->bindParam(":gpsLon", $photoData["gpsLon"], SQLITE3_FLOAT);
        $statement->bindParam(":gpsAlt", $photoData["gpsAlt"], SQLITE3_FLOAT);

        $statement->execute();
        $photoData["id"] = self::$pdb->lastInsertRowID();

        self::AddPhotoToAlbum($photoData["id"], $albumID, $order);

        if ($photoData["tags"] != "") {
            $tags = explode(", ", $photoData["tags"]);
            foreach ($tags as $tag) {
                self::TagPhoto($photoData["id"], $tag);
            }
        }

        return $photoData["id"];
    }

    static function GetPhoto($id) {
        $query = "SELECT * FROM photos WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $id, SQLITE3_INTEGER);
        $results = $statement->execute();
        $photoData = $results->fetchArray(SQLITE3_ASSOC);
        if ($photoData == false) {
            return null;
        }
        if ($photoData["tags"] == "") {
            $photoData["tags"] = [];
        }
        else {
            $photoData["tags"] = explode(", ", $photoData["tags"]);
        }
        return $photoData;
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

        $query = "DELETE FROM photos_tags WHERE photo_id = ?";
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

    static function _getTagID($tag) {
        $query = "SELECT COUNT(*) as count, id FROM tags WHERE tag = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $tag, SQLITE3_TEXT);
        $result = $statement->execute();

        $data = $result->fetchArray(SQLITE3_ASSOC);
        if ($data["count"] == 0) {
            return -1;
        }
        return $data["id"];
    }

    static function TagPhoto($photoID, $tag) {
        $tagID = self::_getTagID($tag);
        if ($tagID == -1) {
            $query = "INSERT INTO tags (tag) VALUES (?);";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $tag, SQLITE3_TEXT);
            $statement->execute();
            $tagID = self::$pdb->lastInsertRowID();
        }

        $query =
            "INSERT OR IGNORE INTO photos_tags (photo_id, tag_id) VALUES (?, ?);";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoID, SQLITE3_INTEGER);
        $statement->bindParam(2, $tagID, SQLITE3_INTEGER);
        $statement->execute();

        if (self::$pdb->changes() > 0) {
            $photoData = self::GetPhoto($photoID);
            $existingTags = $photoData["tags"];
            $idx = array_search($tag, $existingTags);
            if ($idx === false) {
                array_push($existingTags, $tag);
                $tagString = implode(", ", $existingTags);
                $query = "UPDATE photos SET tags = ? WHERE id = ?;";
                $statement = self::$pdb->prepare($query);
                $statement->bindParam(1, $tagString, SQLITE3_TEXT);
                $statement->bindParam(2, $photoID, SQLITE3_INTEGER);
                $statement->execute();
            }
        }
    }

    static function UntagPhoto($photoID, $tag) {
        $tagID = self::_getTagID($tag);
        if ($tagID < 0) {
            return;
        }
        $query = "DELETE FROM photos_tags WHERE photo_id = ? AND tag_id = ?;";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $photoID, SQLITE3_INTEGER);
        $statement->bindParam(2, $tagID, SQLITE3_INTEGER);
        $statement->execute();

        if (self::$pdb->changes() > 0) {
            $photoData = self::GetPhoto($photoID);
            $existingTags = $photoData["tags"];
            $idx = array_search($tag, $existingTags);
            if ($idx !== false) {
                unset($existingTags[$idx]);
            }

            $tagString = implode(", ", $existingTags);
            $query = "UPDATE photos SET tags = ? WHERE id = ?;";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $tagString, SQLITE3_TEXT);
            $statement->bindParam(2, $photoID, SQLITE3_INTEGER);
            $statement->execute();
        }
    }

    static function GetPhotosTagged($tag) {
        $tagID = self::_getTagID($tag);
        if ($tagID < 0) {
            return [];
        }

        $query =
            "SELECT photos.* FROM photos_tags ";
        $query .= "JOIN photos ON photos.id = photos_tags.photo_id ";
        $query .= "WHERE photos_tags.tag_id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $tagID);
        $results = $statement->execute();

        $taggedPhotos = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            if ($row["tags"] == "") {
                $row["tags"] = [];
            }
            else {
                $row["tags"] = explode(", ", $row["tags"]);
            }
            array_push($taggedPhotos, $row);
        }
        return $taggedPhotos;
    }

    static function GetAlbumList($includePrivate = false) {
        $albumFields = [
            "albums.id AS albumId",
            "albums.title as albumTitle",
            "slug",
            "isPrivate",
            "description",
            "ordering",
            "coverPhoto",
            "showMap"
        ];
        $prepCommand = "SELECT " . implode(", ", $albumFields);
        $prepCommand .= ", '::', photos.*";
        $prepCommand .= " FROM albums LEFT OUTER JOIN photos ON photos.id = albums.coverPhoto";
        if (!$includePrivate) {
            $prepCommand .= " WHERE albums.isPrivate != 1";
        }
        $prepCommand .= " ORDER BY albums.ordering ASC, albums.id ASC";
        $statement = self::$pdb->prepare($prepCommand);
        $results = $statement->execute();

        $ret = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $albumData = array_slice($row, 0, count($albumFields));
            $albumData["id"] = $albumData["albumId"];
            $albumData["title"] = $albumData["albumTitle"];
            unset($albumData["albumId"]);
            unset($albumData["albumTitle"]);
            if ($albumData["coverPhoto"] == -1) {
                $albumData["coverPhoto"] = null;
            }
            else {
                $albumData["coverPhoto"] = array_slice($row, (count($albumFields)+1));
            }
            array_push($ret, $albumData);
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
        $sg = new SlugGenerator();
        $slug = $sg->generate($title);

        $query =
            "UPDATE albums SET title = ?, slug = ?, description = ?, isPrivate = ?, showMap = ?, coverPhoto = ? WHERE id = ?";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $title, SQLITE3_TEXT);
        $statement->bindParam(2, $slug, SQLITE3_TEXT);
        $statement->bindParam(3, $description, SQLITE3_TEXT);
        $statement->bindParam(4, $isPrivate, SQLITE3_INTEGER);
        $statement->bindParam(5, $showMap, SQLITE3_INTEGER);
        $statement->bindParam(6, $coverPhoto, SQLITE3_INTEGER);
        $statement->bindParam(7, $id, SQLITE3_INTEGER);
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

        self::$pdb->exec("BEGIN IMMEDIATE TRANSACTION;");
        if ($order == null) {
            $query =
                "SELECT MAX(ordering) FROM photos_albums WHERE album_id = ?;";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $albumID, SQLITE3_INTEGER);
            $results = $statement->execute();
            $max = $results->fetchArray(SQLITE3_NUM)[0];
            if ($max == null) {
                $max = 0;
            }
            $order = $max + 1;
        }

        $statement = self::$pdb->prepare(
            "INSERT INTO photos_albums (photo_id, album_id, ordering) VALUES(?, ?, ?)",
        );
        $statement->bindParam(1, $photoID, SQLITE3_INTEGER);
        $statement->bindParam(2, $albumID, SQLITE3_INTEGER);
        $statement->bindParam(3, $order, SQLITE3_INTEGER);
        try {
            $result = $statement->execute();
            self::$pdb->exec("COMMIT TRANSACTION;");
            if ($result == false) {
                return false;
            }
        } catch (\Throwable $th) {
            self::$pdb->exec("COMMIT TRANSACTION;");
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

    static function MovePhotos($photoIDs, $fromAlbumID, $toAlbumID) {
        self::$pdb->exec("BEGIN IMMEDIATE TRANSACTION;");

        $query = "SELECT MAX(ordering) FROM photos_albums WHERE album_id = ?;";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $toAlbumID, SQLITE3_INTEGER);
        $results = $statement->execute();
        $max = $results->fetchArray(SQLITE3_NUM)[0];
        if ($max == null) {
            $max = 0;
        }

        // doing a fresh selection instead of relying on the order they were passed in
        //   (which may be the order they were selected in a GUI or something; want to
        //    maintain the set ordering from the source album)
        $query = "SELECT * FROM photos_albums WHERE album_id = ?";
        // $photoIDs has already been filtered to be array of numeric values
        $query .=
            " and photo_id IN (" . implode(", ", $photoIDs) . ") ORDER BY ordering ASC";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $fromAlbumID, SQLITE3_INTEGER);
        $beforeMoveResults = $statement->execute();

        $moveQuery =
            "UPDATE photos_albums SET album_id = ?, ordering = ? WHERE photo_id = ? AND album_id = ?;";
        $moveStatement = self::$pdb->prepare($moveQuery);
        $moveStatement->bindParam(1, $toAlbumID, SQLITE3_INTEGER);
        $moveStatement->bindParam(4, $fromAlbumID, SQLITE3_INTEGER);
        $current = $max + 1;
        // could this be more clever?
        while ($row = $beforeMoveResults->fetchArray(SQLITE3_ASSOC)) {
            $moveStatement->bindParam(2, $current, SQLITE3_INTEGER);
            $moveStatement->bindParam(3, $row["photo_id"], SQLITE3_INTEGER);
            $moveStatement->execute();
            $moveStatement->reset();
            $current += 1;
        }

        $query =
            "UPDATE albums SET coverPhoto = -1 WHERE id = ? AND coverPhoto in (" . implode(", ", $photoIDs) . ")";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $fromAlbumID, SQLITE3_INTEGER);
        $results = $statement->execute();

        self::$pdb->exec("COMMIT TRANSACTION;");
    }

    static function GetPhotosInAlbum($albumID) {
        $query = "SELECT photos_albums.ordering, photos.* FROM photos_albums ";
        $query .= "JOIN photos ON photos.id = photos_albums.photo_id ";
        $query .= "WHERE photos_albums.album_id = ? ORDER BY photos_albums.ordering";
        $statement = self::$pdb->prepare($query);
        $statement->bindParam(1, $albumID, SQLITE3_INTEGER);

        $results = $statement->execute();
        $ret = [];
        if ($results == false) {
            return $ret;
        }
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            if ($row["tags"] == "") {
                $row["tags"] = [];
            }
            else {
                $row["tags"] = explode(", ", $row["tags"]);
            }
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

    static function FindAlbum($identifier, $includePhotos = true) {
        if (is_numeric($identifier)) {
            $query = "SELECT * FROM albums WHERE id = ?";
            $statement = self::$pdb->prepare($query);
            $statement->bindParam(1, $identifier, SQLITE3_INTEGER);
        }
        else {
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
            $photos = self::GetPhotosInAlbum($albumData["id"]);
            $albumData["photos"] = $photos;
        }

        return $albumData;
    }
}
