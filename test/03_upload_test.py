import os
import hashlib
import tempfile
import mimetypes

import requests
import pytest

from test_util import stat_assert, get_img_path

def test_public_upload(server, req):
    with open("./test_corpus/jpeg/01.jpeg", "rb") as f:
        res = req.post(
            server.api("/upload"),
            files={"mediaUp": ("01.jpeg", f, "image/jpeg")},
        )
    stat_assert(res, 401)

def test_credentialed_upload(server, auth, req):
    with open("./test_corpus/jpeg/01.jpeg", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("01.jpeg", f, "image/jpeg")},
        )
    stat_assert(res, 200)

def test_non_image_upload(server, auth, req):
    with open("./test_corpus/attributions.txt", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("attributions.txt", f, "text/plain")},
        )
    stat_assert(res, 415)

def test_bad_format_upload(server, auth, req):
    with open("./test_corpus/png/01.png", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("01.png", f, "image/png")},
        )
    stat_assert(res, 415)

def test_multiple_uploads(server, auth, req):
    for i in range(2, 11):
        fname = f"./test_corpus/jpeg/{i:02}.jpeg"
        if not os.path.exists(fname):
            fname = f"./test_corpus/jpeg/{i:02}.jpg"
        with open(fname, "rb") as f:
            md5 = hashlib.md5()
            for segment in iter(lambda: f.read(4096), b""):
                md5.update(segment)
            f.seek(0)
            res = req.post(
                server.api("/upload"),
                headers=auth,
            files={"mediaUp": (os.path.basename(fname), f, mimetypes.guess_type(fname)[0])},
            )
        stat_assert(res, 200)
        assert res.json()["hash"] == md5.hexdigest()

def test_resize_comprehensiveness(server, req):
    res = req.get(server.api("/info"))
    stat_assert(res, 200)
    sizes = res.json()["sizes"]

    res = req.get(server.api(f"/album/view/1"))
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][0]["photos"]

    for i in range(1, 11):
        pdata = list(filter(lambda x: x["id"] == i, plist))[0]
        for s in sizes:
            ipath = get_img_path(s["label"], pdata["hash"], pdata["uniq"])
            res = req.head(server.access(ipath))
            stat_assert(res, 200)

def test_orig_getback(server, req):
    res = req.get(server.api(f"/album/view/1"))
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][0]["photos"]

    for i in range(1, 11):
        pdata = list(filter(lambda x: x["id"] == i, plist))[0]

        with tempfile.TemporaryFile("w+b") as tf:
            with req.get(server.api(f"/photo/orig/{i}"), stream=True) as res:
                stat_assert(res, 200)
                for segment in res.iter_content(chunk_size=4096):
                    tf.write(segment)
            tf.seek(0)
            md5 = hashlib.md5()
            for segment in iter(lambda: tf.read(4096), b""):
                md5.update(segment)
            assert md5.hexdigest() == pdata["hash"]

def test_duplicate_allowed_but_unique(server, auth, req):
    fname = f"./test_corpus/jpeg/01.jpeg"
    with open(fname, "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("01.jpeg", f, "image/jpeg")},
        )
    stat_assert(res, 200)
    dupe_data = res.json()

    res = req.get(server.api(f"/album/view/1"))
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][0]["photos"]

    orig_data = list(filter(lambda x: x["id"] == 1, plist))[0]

    assert orig_data["hash"] == dupe_data["hash"]
    assert orig_data["uniq"] != dupe_data["uniq"]


