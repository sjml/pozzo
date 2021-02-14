import os
import logging
import time

import requests
import pytest

def test_fresh_server_setup(server):
    res = requests.get(server.api("/info"))
    assert res.status_code == 200
    siteInfo = res.json()
    assert siteInfo["siteTitle"] == False

def test_setup(server):
    data = {}
    res = requests.post(server.api("/setup"), json=data)
    assert res.status_code == 400

    data["siteTitle"] = "Pozzo Test Site"
    res = requests.post(server.api("/setup"), json=data)
    assert res.status_code == 400

    data["userName"] = "test_user"
    res = requests.post(server.api("/setup"), json=data)
    assert res.status_code == 400

    data["password"] = "bad_password"
    res = requests.post(server.api("/setup"), json=data)
    assert res.status_code == 200

    res = requests.post(server.api("/setup"), json=data)
    assert res.status_code == 403

def test_login(server):
    data = {}
    res = requests.post(server.api("/login"), json=data)
    assert res.status_code == 400

    data["userName"] = "test_user"
    res = requests.post(server.api("/login"), json=data)
    assert res.status_code == 400

    data["password"] = "wrong_password"
    res = requests.post(server.api("/login"), json=data)
    assert res.status_code == 403

    data["userName"] = "no_such_user"
    res = requests.post(server.api("/login"), json=data)
    assert res.status_code == 404

    data["userName"] = "test_user"
    data["password"] = "bad_password"
    res = requests.post(server.api("/login"), json=data)
    assert res.status_code == 200

def test_key_check(server):
    data = {}
    data["userName"] = "test_user"
    data["password"] = "bad_password"
    res = requests.post(server.api("/login"), json=data)
    assert res.status_code == 200

    token = res.json()["token"]
    assert type(token) is str

    res = requests.post(server.api("/login/check"))
    assert res.status_code == 401
    assert res.json()["reason"] == "missing auth headers"

    res = requests.post(server.api("/login/check"), headers={"Authorization": ""})
    assert res.status_code == 401
    assert res.json()["reason"] == "missing bearer token"

    res = requests.post(server.api("/login/check"), headers={"Authorization": "Bearer not really a token"})
    assert res.status_code == 401
    assert res.json()["reason"] == "invalidToken"

    time.sleep(1)

    res = requests.post(server.api("/login/check"), headers={"Authorization": f"Bearer {token}"})
    assert res.status_code == 200
    assert res.json()["message"] == "Login valid"
    assert res.json()["newToken"] != token

    token = res.json()["newToken"]
    res = requests.post(server.api("/login/check"), headers={"Authorization": f"Bearer {token}"})
    assert res.status_code == 200
    assert res.json()["message"] == "Login valid"

