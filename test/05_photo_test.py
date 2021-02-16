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

