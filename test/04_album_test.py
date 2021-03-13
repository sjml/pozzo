import os
import json
import mimetypes

import requests
import pytest

from test_util import stat_assert, create_album

def test_empty_list_view(server, req):
    res = req.get(
        server.api("/album/list"),
    )
    stat_assert(res, 200)
    assert len(res.json()) == 0

def test_create_album_auth(server, auth, req):
    res = req.post(
        server.api("/album/new")
    )
    stat_assert(res, 401)

def test_create_album(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 1"}
    )
    stat_assert(res, 200)
    assert res.json()["albumID"] == 1

    res = req.get(
        server.api("/album/list"),
    )
    stat_assert(res, 200)
    assert len(res.json()) == 1
    assert res.json()[0]["id"] == 1
    assert res.json()[0]["title"] == "Test Album 1"

def test_album_view(server, req):
    res = req.get(
        server.api("/album/view/1")
    )
    stat_assert(res, 200)
    adata = res.json()

    assert adata["id"] == 1
    assert adata["title"] == "Test Album 1"
    assert len(adata["photoGroups"]) == 1
    assert adata["photoGroups"][0]["id"] == 1
    assert len(adata["photoGroups"][0]["photos"]) == 0

def test_album_view_by_slug(server, req):
    res = req.get(
        server.api("/album/view/test-album-1")
    )
    stat_assert(res, 200)
    adata = res.json()

    assert adata["id"] == 1
    assert adata["title"] == "Test Album 1"
    assert len(adata["photoGroups"]) == 1
    assert adata["photoGroups"][0]["id"] == 1
    assert len(adata["photoGroups"][0]["photos"]) == 0

def test_nonexistent_album_is_nonexistent(server, req):
    res = req.post(
        server.api("/album/view/2")
    )
    stat_assert(res, 404)

def test_album_unique_name(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 1"}
    )
    stat_assert(res, 400)

def test_album_non_numeric_name(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "42"}
    )
    stat_assert(res, 400)

def test_copy_photos_auth(server, req):
    res = req.post(
        server.api("/photo/copy")
    )
    stat_assert(res, 401)

def test_copy_photos(server, auth, req):
    res = req.post(
        server.api("/photo/copy"),
        headers=auth,
    )
    stat_assert(res, 400)

    copyInsts = [{"photoID": x, "groupID": 1} for x in range(1,6)]
    res = req.post(
        server.api("/photo/copy"),
        headers=auth,
        json={
           "copies": copyInsts
        }
    )
    stat_assert(res, 200)
    assert res.json()["numErrors"] == 0

    res = req.get(
        server.api("/album/view/1")
    )
    stat_assert(res, 200)
    adata = res.json()
    plist = adata["photoGroups"][0]["photos"]
    assert len(plist) == 5

def test_remove_photos_auth(server, req):
    res = req.post(
        server.api("/album/remove")
    )
    stat_assert(res, 401)

def test_remove_photos(server, auth, req):
    res = req.post(
        server.api("/album/remove"),
        headers=auth
    )
    stat_assert(res, 400)

    removeInsts = [{"photoID": x, "groupID": 1} for x in range(3,6)]
    res = req.post(
        server.api("/album/remove"),
        headers=auth,
        json={
            "removals": removeInsts
        }
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/1")
    )
    stat_assert(res, 200)
    assert len(res.json()["photoGroups"][0]["photos"]) == 2

def test_create_private_album(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 2", "isPrivate": 1}
    )
    stat_assert(res, 200)

def test_album_privacy(server, auth, req):
    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 404)

    res = req.get(
        server.api("/album/view/2"),
        headers=auth
    )
    stat_assert(res, 200)

def test_album_list_privacy(server, auth, req):
    res = req.get(
        server.api("/album/list"),
    )
    stat_assert(res, 200)
    assert len(res.json()) == 1

    res = req.get(
        server.api("/album/list"),
        headers=auth
    )
    stat_assert(res, 200)
    assert len(res.json()) == 2

def test_album_metadata_auth(server, req):
    res = req.post(
        server.api("/album/edit/2"),
    )
    stat_assert(res, 401)

@pytest.fixture(scope="module")
def ta3(server, auth):
    return create_album("Test Album 3", [[2,3,4,5,6]], server, auth)

def test_album_metadata_empty(server, auth, req, ta3):
    res = req.post(
        server.api(f"/album/edit/{ta3}"),
        headers=auth
    )
    stat_assert(res, 400)

def test_album_metadata_nonexistent(server, auth, req):
    res = req.post(
        server.api("/album/edit/17"),
        headers=auth,
        json={
            "title": "Edited Title",
            "description": "**New description.**",
            "isPrivate": 1,
            "hasMap": 1,
            "coverPhoto": 1
        }
    )
    stat_assert(res, 404)

def test_album_metadata(server, auth, req, ta3):
    res = req.get(
        server.api(f"/album/view/{ta3}")
    )
    stat_assert(res, 200)
    adata = res.json()
    assert adata["title"] == "Test Album 3"
    assert adata["description"] == ""
    assert adata["isPrivate"] == False
    assert adata["hasMap"] == False
    assert adata["coverPhoto"] == -1

    res = req.post(
        server.api(f"/album/edit/{ta3}"),
        headers=auth,
        json={
            "title": "Edited Title",
            "description": "**New description.**",
            "isPrivate": 1,
            "hasMap": 1,
            "coverPhoto": 1
        }
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{ta3}"),
        headers=auth
    )
    stat_assert(res, 200)
    adata = res.json()
    assert adata["title"] == "Edited Title"
    assert adata["description"] == "**New description.**"
    assert adata["isPrivate"] == True
    assert adata["hasMap"] == True
    assert adata["coverPhoto"] == 1

def test_album_invalid_metadata(server, req, auth, ta3):
    res = req.post(
        server.api(f"/album/edit/{ta3}"),
        headers=auth,
        json={
            "title": 5,
            "description": "**New description.**",
            "isPrivate": "false",
            "hasMap": "nah",
            "coverPhoto": "number won"
        }
    )
    stat_assert(res, 400)

def test_reorder_album_list_auth(server, req):
    res = req.post(
        server.api("/album/reorderList"),
        json={"newOrdering": [3,2,1]}
    )
    stat_assert(res, 401)

def test_reorder_album_list_missing_params(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth
    )
    stat_assert(res, 400)

def test_reorder_album_list_not_enough(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [2,1]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_album_list_too_much(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [4,3,2,1]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_album_list_bad_index(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [3,2,7]}
    )
    stat_assert(res, 400)
    assert "Misplaced" in res.json()["message"]

def test_reorder_album_list_non_unique(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [3,2,2]}
    )
    stat_assert(res, 400)
    assert "Non-unique" in res.json()["message"]

def test_reorder_album_list(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [3,2,1]}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/list"),
        headers=auth
    )
    stat_assert(res, 200)
    order = [a["id"] for a in res.json()]
    assert order == [3,2,1]

# also checking the special cases to make sure all the code is covered
def test_ordered_upload(server, auth, req):
    ordered_id = create_album("Ordered Album", [], server, auth)

    files = os.listdir("./test_corpus/test_cases")
    files.sort()
    fcount = len(files)
    for i, img in enumerate(files):
        with open(f"./test_corpus/test_cases/{img}", "rb") as f:
            res = req.post(
                server.api("/upload"),
                headers=auth,
                files={"mediaUp": (img, f, mimetypes.guess_type(img)[0])},
                data={"data": json.dumps({"albumID": ordered_id, "order": fcount - i})}
            )
        stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{ordered_id}")
    )
    stat_assert(res, 200)
    adata = res.json()
    assert adata["id"] == ordered_id
    gdata = adata["photoGroups"][0]
    assert len(gdata["photos"]) == 2
    assert gdata["photos"][0]["id"] == 13
    assert gdata["photos"][1]["id"] == 12

def test_delete_album_auth(server, req, ta3):
    res = req.post(
        server.api("/album/delete"),
        json={"albumID": ta3}
    )
    stat_assert(res, 401)

def test_delete_nonexistent_album(server, auth, req, ta3):
    res = req.post(
        server.api("/album/delete"),
        headers=auth,
        json={"albumID": ta3 + 5}
    )
    stat_assert(res, 404)

def test_delete_album_params(server, auth, req):
    res = req.post(
        server.api("/album/delete"),
        headers=auth,
    )
    stat_assert(res, 400)

def test_delete_album(server, auth, req, ta3):
    res = req.post(
        server.api("/album/delete"),
        headers=auth,
        json={"albumID": ta3}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{ta3}"),
        headers=auth
    )
    stat_assert(res, 404)
