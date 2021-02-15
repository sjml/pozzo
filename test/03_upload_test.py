import os
import hashlib
import textwrap
import tempfile

import requests
import pytest

def test_public_upload(server):
    with open("./test_corpus/jpeg/01.jpeg", "rb") as f:
        res = requests.post(
            server.api("/upload"),
            files={"photoUp": f}
        )
    assert res.status_code == 401

def test_credentialed_upload(server, auth):
    with open("./test_corpus/jpeg/01.jpeg", "rb") as f:
        res = requests.post(
            server.api("/upload"),
            headers=auth,
            files={"photoUp": f},
        )
    assert res.status_code == 200

def test_non_image_upload(server, auth):
    with open("./test_corpus/attributions.txt", "rb") as f:
        res = requests.post(
            server.api("/upload"),
            headers=auth,
            files={"photoUp": f},
        )
    assert res.status_code == 415

def test_bad_format_upload(server, auth):
    with open("./test_corpus/png/01.png", "rb") as f:
        res = requests.post(
            server.api("/upload"),
            headers=auth,
            files={"photoUp": f},
        )
    assert res.status_code == 415

def test_multiple_uploads(server, auth):
    for i in range(2, 11):
        fname = f"./test_corpus/jpeg/{i:02}.jpeg"
        if not os.path.exists(fname):
            fname = f"./test_corpus/jpeg/{i:02}.jpg"
        with open(fname, "rb") as f:
            md5 = hashlib.md5()
            for segment in iter(lambda: f.read(4096), b""):
                md5.update(segment)
            f.seek(0)
            res = requests.post(
                server.api("/upload"),
                headers=auth,
                files={"photoUp": f},
            )
        assert res.status_code == 200
        assert res.json()["hash"] == md5.hexdigest()

def get_img_path(size, phash, uniq):
    raw_dirs = textwrap.wrap(phash, 2)[:3]
    dirs = [d.replace("ad", "a_") for d in raw_dirs]
    return f"/photos/{'/'.join(dirs)}/{phash}_{uniq}_{size}.jpg"

def test_resize_comprehensiveness(server):
    res = requests.get(server.api("/info"))
    assert res.status_code == 200
    sizes = res.json()["sizes"]

    for i in range(1, 11):
        res = requests.get(server.api(f"/photo/view/{i}"))
        assert res.status_code == 200
        pdata = res.json()
        for s in sizes:
            ipath = get_img_path(s["label"], pdata["hash"], pdata["uniq"])
            res = requests.head(server.access(ipath))
            assert res.status_code == 200

def test_orig_getback(server):
    for i in range(1, 11):
        res = requests.get(server.api(f"/photo/view/{i}"))
        assert res.status_code == 200
        pdata = res.json()

        with tempfile.TemporaryFile("w+b") as tf:
            with requests.get(server.api(f"/photo/orig/{i}"), stream=True) as req:
                assert req.status_code == 200
                for segment in req.iter_content(chunk_size=4096):
                    tf.write(segment)
            tf.seek(0)
            md5 = hashlib.md5()
            for segment in iter(lambda: tf.read(4096), b""):
                md5.update(segment)
            assert md5.hexdigest() == pdata["hash"]

def test_non_existent_image_is_nonexistent(server):
    res = requests.get(server.api("/photo/view/11"))
    assert res.status_code == 404
