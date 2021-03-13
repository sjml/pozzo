import os
import json

import requests
import pytest

from test_util import stat_assert, create_album

@pytest.fixture(scope="module")
def priv(server, auth):
    aid = create_album("Private Album", [], server, auth)
    res = requests.post(
        server.api(f"/album/edit/{aid}"),
        headers=auth,
        json={
            "isPrivate": 1,
        }
    )
    stat_assert(res, 200)
    return aid

@pytest.fixture(scope="module")
def pub(server, auth):
    return create_album("Public Album", [], server, auth)


def test_private_upload(server, auth, req, priv, pub):
    with open("./test_corpus/jpeg/01.jpeg", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("01.jpeg", f, "image/jpeg")}
        )
    stat_assert(res, 200)
    unsorted_id = res.json()["id"]

    with open("./test_corpus/jpeg/04.jpeg", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("04.jpeg", f, "image/jpeg")},
            data={"data": json.dumps({"albumID": pub})},
        )
    stat_assert(res, 200)
    pub_id = res.json()["id"]

    with open("./test_corpus/jpeg/03.jpeg", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("03.jpeg", f, "image/jpeg")},
            data={"data": json.dumps({"albumID": priv})},
        )
    stat_assert(res, 200)
    priv_id = res.json()["id"]

    with open("./test_corpus/jpeg/02.jpeg", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("02.jpeg", f, "image/jpeg")},
            data={"data": json.dumps({"albumID": priv})},
        )
    stat_assert(res, 200)
    both_id = res.json()["id"]

    res = req.post(
        server.api(f"/album/view/{pub}")
    )
    stat_assert(res, 200)
    gid = res.json()["photoGroups"][0]["id"]

    res = req.post(
        server.api("/photo/copy"),
        headers=auth,
        json={
           "copies": [{"photoID": both_id, "groupID": gid}]
        }
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/dynamic/all"),
        headers=auth,
    )
    stat_assert(res, 200)
    pids = [p["id"] for p in res.json()]
    assert pub_id in pids
    assert both_id in pids
    assert priv_id in pids

    res = req.get(
        server.api("/dynamic/all"),
    )
    stat_assert(res, 200)
    pids = [p["id"] for p in res.json()]
    assert pub_id in pids
    assert both_id in pids
    assert priv_id not in pids
