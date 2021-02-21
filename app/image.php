<?php

use kornrunner\Blurhash\Blurhash;

require_once __DIR__ . "/db.php";

const sizes = [
    [
        "maxHeight" => 2160,
        "maxWidth" => 3840,
        "label" => "large2x",
    ],
    [
        "maxHeight" => 1440,
        "maxWidth" => 2560,
        "label" => "medium2x",
    ],
    [
        "maxHeight" => 1080,
        "maxWidth" => 1920,
        "label" => "large",
    ],
    [
        "maxHeight" => 720,
        "maxWidth" => 1280,
        "label" => "medium",
    ],
    [
        "maxHeight" => 300,
        "maxWidth" => 300,
        "label" => "small2x",
    ],
    [
        "maxHeight" => 150,
        "maxWidth" => 150,
        "label" => "small",
    ],
    [
        "maxHeight" => 64,
        "maxWidth" => 64,
        "label" => "tiny2x",
    ],
    [
        "maxHeight" => 32,
        "maxWidth" => 32,
        "label" => "tiny",
    ],
];

function getImagePath($sizeLabel, $hash, $uniq, $extension = "jpg") {
    $ret = __DIR__ . "/../public/photos/";
    $rawDirs = str_split($hash, 2);
    $rawDirs = array_slice($rawDirs, 0, 3);
    $dirs = [];
    foreach ($rawDirs as $d) {
        // having directories called "ad" in an image path
        //   can trigger some adblockers
        if ($d == "ad") {
            array_push($dirs, "a_");
        } else {
            array_push($dirs, $d);
        }
    }

    $ret .= implode("/", $dirs);
    if (!is_dir($ret)) {
        mkdir($ret, 0755, true);
    }
    return $ret .
        "/" .
        $hash .
        "_" .
        $uniq .
        "_" .
        $sizeLabel .
        "." .
        $extension;
}

function deleteImagesWithHash($hash, $uniq) {
    $delSizes = array_column(sizes, "label");
    array_push($delSizes, "orig");
    foreach ($delSizes as $size) {
        $path = getImagePath($size, $hash, $uniq);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

function importImage($filePath) {
    if (exif_imagetype($filePath) != IMAGETYPE_JPEG) {
        return null;
    }
    $photoData = [
        "title" => basename($filePath),
        "size" => filesize($filePath),
        "hash" => md5_file($filePath),
        "uniq" => uniqid(),
    ];

    $origPath = getImagePath("orig", $photoData["hash"], $photoData["uniq"]);
    move_uploaded_file($filePath, $origPath);

    return $photoData;
}

function importVideo($filePath, $origName) {
    $photoData = [
        "title" => basename($filePath),
        "size" => filesize($filePath),
        "hash" => md5_file($filePath),
        "uniq" => uniqid(),
    ];

    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $origPath = getImagePath(
        "orig",
        $photoData["hash"],
        $photoData["uniq"],
        $ext,
    );
    move_uploaded_file($filePath, $origPath);

    $ffprobe = FFMpeg\FFProbe::create();
    $duration = $ffprobe->format($origPath)->get("duration");
    $ffmpeg = FFMpeg\FFMpeg::create();
    $video = $ffmpeg->open($origPath);
    $preview = $video->frame(
        FFMpeg\Coordinate\TimeCode::fromSeconds($duration / 3),
    );
    $previewPath = getImagePath("orig", $photoData["hash"], $photoData["uniq"]);
    $preview->save($previewPath);

    return $photoData;
}

function processImage(&$photoData) {
    $origPath = getImagePath("orig", $photoData["hash"], $photoData["uniq"]);

    $img = new IMagick();

    if (!isset($photoData["width"])) {
        $img->readImage($origPath);
        $photoData["width"] = $img->getImageWidth();
        $photoData["height"] = $img->getImageHeight();
        $photoData["aspect"] =
            (float) $img->getImageWidth() / (float) $img->getImageHeight();
    }

    foreach (sizes as $size) {
        $img->setOption(
            "jpeg:size",
            $size["maxWidth"] . "x" . $size["maxHeight"],
        );

        $img->readImage($origPath);

        // Quality setting is higher than it *needs* to be by any strict definition,
        //   but for gallery purposes I'd rather err on the side of images being
        //   slightly too large.
        // Very hard to pick a single number that's good for all images.
        //   Given how image-heavy this site will be, it'll never really have
        //   perfect lighthouse scores unless we're willing to degrade the visuals,
        //   which... not today.
        $img->setImageCompressionQuality(88);

        // correct orientation directly
        $orient = $img->getImageOrientation();
        switch ($orient) {
            case imagick::ORIENTATION_RIGHTTOP:
                $img->rotateImage("#00000000", 90);
                break;
            case imagick::ORIENTATION_BOTTOMRIGHT:
                $img->rotateImage("#00000000", 180);
                break;
            case imagick::ORIENTATION_LEFTBOTTOM:
                $img->rotateImage("#00000000", 270);
                break;
        }
        $img->setImageOrientation(imagick::ORIENTATION_TOPLEFT);

        $profiles = $img->getImageProfiles("icc", true);

        $img->scaleImage($size["maxWidth"], $size["maxHeight"], true);
        $img->stripImage();

        // all other profiles were removed when the image was stripped;
        //   add back the ICC one if it existed. Leads to some cross-browser
        //   inconsistency, but worth maintaining accuracy if it's possible!
        if (!empty($profiles)) {
            $img->profileImage("icc", $profiles["icc"]);
        }

        $img->writeImage(
            getImagePath(
                $size["label"],
                $photoData["hash"],
                $photoData["uniq"],
            ),
        );
    }

    $img->setOption(
        "jpeg:size",
        sizes[count(sizes) - 1]["maxWidth"] .
            "x" .
            sizes[count(sizes) - 1]["maxHeight"],
    );
    $img->readImage($origPath);

    $blur = imagecreatefromstring(
        file_get_contents(
            getImagePath("tiny", $photoData["hash"], $photoData["uniq"]),
        ),
    );
    $blurW = imagesx($blur);
    $blurH = imagesy($blur);

    $pixels = [];
    for ($y = 0; $y < $blurH; ++$y) {
        $row = [];
        for ($x = 0; $x < $blurW; ++$x) {
            $index = imagecolorat($blur, $x, $y);
            $colors = imagecolorsforindex($blur, $index);
            $row[] = [$colors["red"], $colors["green"], $colors["blue"]];
        }
        $pixels[] = $row;
    }

    // making slightly bigger blurs hash than standard
    if ($photoData["aspect"] > 1.0) {
        $components_x = 6;
        $components_y = max([2, round($components_x / $photoData["aspect"])]);
    } else {
        $components_y = 6;
        $components_x = max([2, round($components_y * $photoData["aspect"])]);
    }
    $photoData["blurHash"] = Blurhash::encode(
        $pixels,
        $components_x,
        $components_y,
    );
}

function processPhotoMeta(&$photoData) {
    $originalFilePath = getImagePath(
        "orig",
        $photoData["hash"],
        $photoData["uniq"],
    );

    $reader = \PHPExif\Reader\Reader::factory(
        \PHPExif\Reader\Reader::TYPE_EXIFTOOL,
    );
    $exif = $reader->read($originalFilePath);

    $rawData = $exif->getRawData();

    $photoData["make"] = $rawData["IFD0:Make"] ?? null;
    $photoData["model"] = $rawData["IFD0:Model"] ?? null;
    $photoData["lens"] = $rawData["ExifIFD:LensModel"] ?? null;
    $photoData["mime"] = $rawData["File:MIMEType"] ?? null;

    $photoData["creationDate"] = $exif->getCreationDate();
    if ($photoData["creationDate"] === false) {
        $photoData["creationDate"] = null;
    } else {
        $photoData["creationDate"] = $photoData["creationDate"]->getTimeStamp();
    }

    $photoData["tags"] = $rawData["IPTC:Keywords"] ?? "";
    if ($photoData["tags"] != null) {
        $photoData["tags"] = implode(", ", $photoData["tags"]);
    }

    $photoData["subjectArea"] = $rawData["ExifIFD:SubjectArea"] ?? null;
    $photoData["aperture"] = $rawData["Composite:Aperture"] ?? null;
    $photoData["iso"] = $rawData["ExifIFD:ISO"] ?? null;
    $photoData["shutterSpeed"] = $rawData["Composite:ShutterSpeed"] ?? null;
    $photoData["gpsLat"] = $rawData["Composite:GPSLatitude"] ?? null;
    $photoData["gpsLon"] = $rawData["Composite:GPSLongitude"] ?? null;
    $photoData["gpsAlt"] = $rawData["Composite:GPSAltitude"] ?? null;
}

function processVideoMeta(&$vidData) {
    $ext = pathinfo($vidData["title"], PATHINFO_EXTENSION);
    $originalFilePath = getImagePath(
        "orig",
        $vidData["hash"],
        $vidData["uniq"],
        $ext,
    );

    $reader = \PHPExif\Reader\Reader::factory(
        \PHPExif\Reader\Reader::TYPE_EXIFTOOL,
    );
    $exif = $reader->read($originalFilePath);

    $rawExif = $exif->getRawData();

    $vidData["make"] = $rawExif["UserData:Make"] ?? null;
    $vidData["model"] = $rawExif["UserData:Model"] ?? null;
    $vidData["mime"] = $rawExif["File:MIMEType"] ?? null;
    $vidData["creationDate"] = $rawExif["Keys:CreationDate"] ?? null;

    $vidData["gpsLat"] = $rawExif["Composite:GPSLatitude"] ?? null;
    $vidData["gpsLon"] = $rawExif["Composite:GPSLongitude"] ?? null;
    $vidData["gpsAlt"] = $rawExif["Composite:GPSAltitude"] ?? null;
}
