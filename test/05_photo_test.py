import os

import requests
import pytest

# most photo operations are tested with the album or upload,
#    so this is pretty minimal.

def test_photo_view(server, req):
    res = req.get(
        server.api("/photo/view/1")
    )
    assert res.status_code == 200

def test_nonexistent_photo_is_nonexistent(server, req):
    res = req.get(
        server.api("/photo/view/25"),
    )
    assert res.status_code == 404

def test_delete_photo_auth(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        json={"photoID": 1}
    )
    assert res.status_code == 401

def test_delete_missing_params(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth
    )
    assert res.status_code == 400

def test_delete_nonexistent_photo(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth,
        json={"photoID": 25}
    )
    assert res.status_code == 404

def test_delete_photo(server, auth, req):
    res = req.post(
        server.api("/photo/delete"),
        headers=auth,
        json={"photoID": 1}
    )
    assert res.status_code == 200

def test_original_nonexistent(server, req):
    res = req.get(server.api(f"/photo/orig/25"))
    assert res.status_code == 404

def test_photo_set_params(server, req):
    res = req.post(server.api("/photo/set"))
    assert res.status_code == 400

def test_photo_set_bad_params(server, req):
    res = req.post(server.api("/photo/set"),
        json={"photoIDs": "a string is wrong"}
    )
    assert res.status_code == 400

    res = req.post(server.api("/photo/set"),
        json={"photoIDs": [1, 2, "three", "so close"]}
    )
    assert res.status_code == 400

def test_photo_set(server, req):
    res = req.post(server.api("/photo/set"),
        json={"photoIDs": [1, 2, 10, 42, 7, 4]}
    )
    assert res.status_code == 200
    photos = res.json()
    assert photos[0] == None
    assert photos[1]["id"] == 2
    assert photos[2]["id"] == 10
    assert photos[3] == None
    assert photos[4]["id"] == 7
    assert photos[5]["id"] == 4

def test_photo_move_auth(server, req):
    res = req.post(
        server.api("/photo/move"),
        json={"photoIDs": [3,4,5], "fromAlbumID": 2, "toAlbumID": 4}
    )
    assert res.status_code == 401

def test_photo_move_params(server, req, auth):
    res = req.post(
        server.api("/photo/move"),
        headers=auth
    )
    assert res.status_code == 400

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": "can't use a string", "fromAlbumID": 2, "toAlbumID": 4}
    )
    assert res.status_code == 400

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [2, 5, "all", "have", "to be numbers"], "fromAlbumID": 2, "toAlbumID": 4}
    )
    assert res.status_code == 400

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4,5], "toAlbumID": 4}
    )
    assert res.status_code == 400

    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4,5], "fromAlbumID": 2}
    )
    assert res.status_code == 400

def test_photo_move(server, req, auth):
    res = req.post(
        server.api("/photo/move"),
        headers=auth,
        json={"photoIDs": [3,4,5], "fromAlbumID": 2, "toAlbumID": 4}
    )
    print(res.content)
    assert res.status_code == 200

    res = req.get(
        server.api("/album/view/ordered-album")
    )
    assert res.status_code == 200
    adata = res.json()
    assert adata["id"] == 4
    assert len(adata["photos"]) == 5
    assert adata["photos"][2]["id"] == 5
    assert adata["photos"][3]["id"] == 4
    assert adata["photos"][4]["id"] == 3
