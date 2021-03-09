import os

import requests
import pytest

from test_util import stat_assert

def test_delete_photo_auth(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        json={"photoIDs": [1]}
    )
    stat_assert(res, 401)

def test_delete_missing_params(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth
    )
    stat_assert(res, 400)

def test_delete_photo(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth,
        json={"photoIDs": [1]}
    )
    stat_assert(res, 200)

def test_original_nonexistent(server, req):
    res = req.get(server.api(f"/photo/orig/25"))
    stat_assert(res, 404)

def test_photo_move_auth(server, req):
    res = req.post(
        server.api("/photo/move"),
        json={"photoIDs": [3,4], "fromGroupID": 6, "toGroupID": 2}
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
        json={"photoIDs": "can't use a string", "fromGroupID": 2, "toGroupID": 4}
    )
    stat_assert(res, 400)
    assert "Invalid or missing parameter 'photoIDs'" in res.json()["message"]

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [2, 5, "all", "have", "to be numbers"], "fromGroupID": 2, "toGroupID": 4}
    )
    stat_assert(res, 400)
    assert "Non-numeric values in photoIDs list" in res.json()["message"]

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4], "toGroupID": 4}
    )
    stat_assert(res, 400)
    assert "Missing or non-numeric value for 'fromGroupID'" in res.json()["message"]

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4], "fromGroupID": 2}
    )
    stat_assert(res, 400)
    assert "Missing or non-numeric value for 'toGroupID'" in res.json()["message"]

def test_photo_move(server, req, auth):
    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4], "fromGroupID": 6, "toGroupID": 2}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/test-album-1")
    )
    stat_assert(res, 200)
    adata = res.json()
    assert adata["id"] == 2

    gdata = adata["photoGroups"][0]
    assert gdata["id"] == 6
    assert len(gdata["photos"]) == 1

    gdata = adata["photoGroups"][2]
    assert gdata["id"] == 2
    assert len(gdata["photos"]) == 3

    plist = gdata["photos"]
    assert plist[0]["id"] == 5
    assert plist[1]["id"] == 3
    assert plist[2]["id"] == 4

def test_photo_tag_import(server, req):
    res = req.get(server.api(f"/album/view/2"))
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][2]["photos"]
    pdata = list(filter(lambda x: x["id"] == 4, plist))[0]

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

    res = req.get(server.api(f"/album/view/2"))
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][2]["photos"]

    pdata = list(filter(lambda x: x["id"] == 3, plist))[0]
    assert "A" in pdata["tags"]

    pdata = list(filter(lambda x: x["id"] == 4, plist))[0]
    assert "A" in pdata["tags"]

    pdata = list(filter(lambda x: x["id"] == 5, plist))[0]
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

    res = req.get(server.api(f"/album/view/2"))
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][2]["photos"]

    pdata = list(filter(lambda x: x["id"] == 4, plist))[0]
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
