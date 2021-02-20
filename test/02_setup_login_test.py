import os
import logging
import time

import requests
import pytest

from test_util import stat_assert

def test_fresh_server_setup(server, req):
    res = req.get(server.api("/info"))
    stat_assert(res, 200)
    siteInfo = res.json()
    assert siteInfo["siteTitle"] == False

def test_setup(server, req):
    data = {}
    res = req.post(server.api("/setup"), json=data)
    stat_assert(res, 400)

    data["siteTitle"] = "Pozzo Test Site"
    res = req.post(server.api("/setup"), json=data)
    stat_assert(res, 400)

    data["userName"] = "test_user"
    res = req.post(server.api("/setup"), json=data)
    stat_assert(res, 400)

    data["password"] = "bad_password"
    res = req.post(server.api("/setup"), json=data)
    stat_assert(res, 200)

def test_no_repeat_setup(server, req):
    data = {"siteTitle": "Set Up Again", "userName": "bad_guy", "password": "even_worse_password"}
    res = req.post(server.api("/setup"), json=data)
    stat_assert(res, 403)
    assert "already set up" in res.json()["message"]

def test_login(server, req):
    data = {}
    res = req.post(server.api("/login"), json=data)
    stat_assert(res, 400)

    data["userName"] = "test_user"
    res = req.post(server.api("/login"), json=data)
    stat_assert(res, 400)

    data["password"] = "wrong_password"
    res = req.post(server.api("/login"), json=data)
    stat_assert(res, 403)

    data["userName"] = "no_such_user"
    res = req.post(server.api("/login"), json=data)
    stat_assert(res, 404)

    data["userName"] = "test_user"
    data["password"] = "bad_password"
    res = req.post(server.api("/login"), json=data)
    stat_assert(res, 200)

def test_key_check(server, req):
    data = {}
    data["userName"] = "test_user"
    data["password"] = "bad_password"
    res = req.post(server.api("/login"), json=data)
    stat_assert(res, 200)

    token = res.json()["token"]
    assert type(token) is str

    res = req.post(server.api("/login/check"))
    stat_assert(res, 401)
    assert res.json()["reason"] == "missing auth headers"

    res = req.post(server.api("/login/check"), headers={"Authorization": ""})
    stat_assert(res, 401)
    assert res.json()["reason"] == "missing bearer token"

    res = req.post(server.api("/login/check"), headers={"Authorization": "Bearer not really a token"})
    stat_assert(res, 401)
    assert res.json()["reason"] == "invalidToken"

    time.sleep(1)

    res = req.post(server.api("/login/check"), headers={"Authorization": f"Bearer {token}"})
    stat_assert(res, 200)
    assert res.json()["message"] == "Login valid"
    assert res.json()["newToken"] != token

    token = res.json()["newToken"]
    res = req.post(server.api("/login/check"), headers={"Authorization": f"Bearer {token}"})
    stat_assert(res, 200)
    assert res.json()["message"] == "Login valid"

