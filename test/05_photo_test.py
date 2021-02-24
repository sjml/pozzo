import os

import requests
import pytest

from test_util import stat_assert

# most photo operations are tested with the album or upload,
#    so this is pretty minimal.

def test_delete_photo_auth(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        json={"photoID": 1}
    )
    stat_assert(res, 401)

def test_delete_missing_params(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth
    )
    stat_assert(res, 400)

def test_delete_nonexistent_photo(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth,
        json={"photoID": 25}
    )
    stat_assert(res, 404)

def test_delete_photo(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth,
        json={"photoID": 1}
    )
    stat_assert(res, 200)

def test_original_nonexistent(server, req):
    res = req.get(server.api(f"/photo/orig/25"))
    stat_assert(res, 404)

def test_photo_move_auth(server, req):
    res = req.post(
        server.api("/photo/move"),
        json={"photoIDs": [3,4,5], "fromAlbumID": 2, "toAlbumID": 4}
    )
    stat_assert(res, 401)

def test_photo_move_params(server, req, auth):
    res = req.post(
        server.api("/photo/move"),
        headers=auth
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": "can't use a string", "fromAlbumID": 2, "toAlbumID": 4}
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [2, 5, "all", "have", "to be numbers"], "fromAlbumID": 2, "toAlbumID": 4}
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4,5], "toAlbumID": 4}
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4,5], "fromAlbumID": 2}
    )
    stat_assert(res, 400)

def test_photo_move(server, req, auth):
    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4,5], "fromAlbumID": 2, "toAlbumID": 4}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/ordered-album")
    )
    stat_assert(res, 200)
    adata = res.json()
    assert adata["id"] == 4
    assert len(adata["photos"]) == 5
    assert adata["photos"][2]["id"] == 5
    assert adata["photos"][3]["id"] == 4
    assert adata["photos"][4]["id"] == 3

def test_photo_tag_import(server, req):
    res = req.get(server.api(f"/album/view/4"))
    stat_assert(res, 200)
    adata = res.json()
    pdata = list(filter(lambda x: x["id"] == 4, adata["photos"]))[0]

    assert len(pdata["tags"]) == 2
    assert "Israel" in pdata["tags"]
    assert "Jerusalem" in pdata["tags"]

def test_photo_tag_auth(server, req):
    res = req.get(
        server.api("/photo/tag"),
        json={"photoIDs": [3, 4, 5], "tags": ["A"]}
    )
    stat_assert(res, 401)

def test_photo_tag(server, req, auth):
    res = req.get(
        server.api("/photo/tag"),
        headers=auth,
        json={"photoIDs": [3, 4, 5], "tags": ["A"]}
    )
    stat_assert(res, 200)

    res = req.get(server.api(f"/album/view/4"))
    stat_assert(res, 200)
    adata = res.json()

    pdata = list(filter(lambda x: x["id"] == 3, adata["photos"]))[0]
    assert "A" in pdata["tags"]

    pdata = list(filter(lambda x: x["id"] == 4, adata["photos"]))[0]
    assert "A" in pdata["tags"]

    pdata = list(filter(lambda x: x["id"] == 5, adata["photos"]))[0]
    assert "A" in pdata["tags"]

    res = req.get(
        server.api("/photo/tagset"),
        json={"tags": ["A"]}
    )
    stat_assert(res, 200)
    tagged_photos = res.json()
    assert len(tagged_photos) == 3
    ids = [tp["id"] for tp in tagged_photos]
    assert 3 in ids
    assert 4 in ids
    assert 5 in ids

def test_photo_untag_auth(server, req):
    res = req.get(
        server.api("/photo/untag"),
        json={"photoIDs": [4], "tags": ["A"]}
    )
    stat_assert(res, 401)

def test_photo_untag(server, req, auth):
    res = req.get(
        server.api("/photo/untag"),
        headers=auth,
        json={"photoIDs": [4], "tags": ["A"]}
    )
    stat_assert(res, 200)

    res = req.get(server.api(f"/album/view/4"))
    stat_assert(res, 200)
    adata = res.json()

    pdata = list(filter(lambda x: x["id"] == 4, adata["photos"]))[0]
    assert "A" not in pdata["tags"]

    res = req.get(
        server.api("/photo/tagset"),
        json={"tags": ["A"]}
    )
    stat_assert(res, 200)
    tagged_photos = res.json()
    assert len(tagged_photos) == 2
    ids = [tp["id"] for tp in tagged_photos]
    assert 4 not in ids
