import requests
import pytest

def test_list_view(server, req):
    res = req.get(
        server.api("/album/list"),
    )
    assert res.status_code == 200
    assert len(res.json()) == 1

def test_album_view(server, req):
    res = req.get(
        server.api("/album/view/1")
    )
    assert res.status_code == 200
    adata = res.json()
    assert adata["title"] == "[unsorted]"
    assert len(adata["photos"]) == 11
    assert "tinyJPEG" not in adata["photos"][0]

def test_album_previews(server, req):
    res = req.post(
        server.api("/album/view/1"),
        json={"previews": 1}
    )
    assert res.status_code == 200
    adata = res.json()
    assert "tinyJPEG" in adata["photos"][0]

def test_nonexistent_album_is_nonexistent(server, req):
    res = req.post(
        server.api("/album/view/2"),
        json={"previews": 1}
    )
    assert res.status_code == 404

def test_create_album_auth(server, auth, req):
    res = req.post(
        server.api("/album/new")
    )
    assert res.status_code == 401

def test_create_album(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth
    )
    assert res.status_code == 400

    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 1"}
    )
    assert res.status_code == 200

    res = req.get(
        server.api("/album/list"),
    )
    assert res.status_code == 200
    assert len(res.json()) == 2
    assert res.json()[1]["title"] == "Test Album 1"

    res = req.get(
        server.api("/album/view/2")
    )
    assert res.status_code == 200
    assert len(res.json()["photos"]) == 0

def test_album_unique_name(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 1"}
    )
    assert res.status_code == 400

def test_album_non_numeric_name(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "42"}
    )
    assert res.status_code == 400

def test_copy_photos_auth(server, req):
    res = req.post(
        server.api("/photo/copy")
    )
    assert res.status_code == 401

def test_copy_photos(server, auth, req):
    res = req.post(
        server.api("/photo/copy"),
        headers=auth,
    )
    assert res.status_code == 400

    copyInsts = [{"photoID": x, "albumID": 2} for x in range(1,6)]
    res = req.post(
        server.api("/photo/copy"),
        headers=auth,
        json={
           "copies": copyInsts
        }
    )
    assert res.status_code == 200
    assert res.json()["numErrors"] == 0

    res = req.get(
        server.api("/album/view/2")
    )
    assert res.status_code == 200
    adata = res.json()
    assert len(adata["photos"]) == 5

def test_remove_photos_auth(server, req):
    res = req.post(
        server.api("/album/remove")
    )
    assert res.status_code == 401

def test_remove_photos(server, auth, req):
    res = req.post(
        server.api("/album/remove"),
        headers=auth
    )
    assert res.status_code == 400

    removeInsts = [{"photoID": x, "albumID": 1} for x in range(1,6)]
    res = req.post(
        server.api("/album/remove"),
        headers=auth,
        json={
            "removals": removeInsts
        }
    )
    assert res.status_code == 200

    res = req.get(
        server.api("/album/view/1")
    )
    assert res.status_code == 200
    assert len(res.json()["photos"]) == 6

def test_create_private_album(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 2", "isPrivate": 1}
    )
    assert res.status_code == 200

def test_album_privacy(server, auth, req):
    res = req.get(
        server.api("/album/view/3")
    )
    assert res.status_code == 404

    res = req.get(
        server.api("/album/view/3"),
        headers=auth
    )
    assert res.status_code == 200

def test_album_list_privacy(server, auth, req):
    res = req.get(
        server.api("/album/list"),
    )
    assert res.status_code == 200
    assert len(res.json()) == 2

    res = req.get(
        server.api("/album/list"),
        headers=auth
    )
    assert res.status_code == 200
    assert len(res.json()) == 3

def test_album_metadata_auth(server, req):
    res = req.post(
        server.api("/album/edit/2"),
    )
    assert res.status_code == 401

def test_album_metadata_empty(server, auth, req):
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Test Album 3"}
    )
    assert res.status_code == 200

    res = req.post(
        server.api("/album/edit/4"),
        headers=auth
    )
    assert res.status_code == 400

def test_album_metadata(server, auth, req):
    res = req.get(
        server.api("/album/view/4")
    )
    assert res.status_code == 200
    adata = res.json()
    assert adata["title"] == "Test Album 3"
    assert adata["description"] == ""
    assert adata["isPrivate"] == False
    assert adata["showMap"] == False
    assert adata["coverPhoto"] == -1

    res = req.post(
        server.api("/album/edit/4"),
        headers=auth,
        json={
            "title": "Edited Title",
            "description": "**New description.**",
            "isPrivate": 1,
            "showMap": 1,
            "coverPhoto": 1
        }
    )
    assert res.status_code == 200

    res = req.get(
        server.api("/album/view/4"),
        headers=auth
    )
    assert res.status_code == 200
    adata = res.json()
    assert adata["title"] == "Edited Title"
    assert adata["description"] == "**New description.**"
    assert adata["isPrivate"] == True
    assert adata["showMap"] == True
    assert adata["coverPhoto"] == 1

def test_delete_album_auth(server, req):
    res = req.post(
        server.api("/album/delete"),
    )
    assert res.status_code == 401

def test_delete_album(server, auth, req):
    res = req.post(
        server.api("/album/delete"),
        headers=auth,
    )
    assert res.status_code == 400

    res = req.post(
        server.api("/album/delete"),
        headers=auth,
        json={"albumID": 7}
    )
    assert res.status_code == 404

    res = req.post(
        server.api("/album/delete"),
        headers=auth,
        json={"albumID": 4}
    )
    assert res.status_code == 200

    res = req.get(
        server.api("/album/view/4"),
        headers=auth
    )
    assert res.status_code == 404

def test_delete_nonexistent_album(server, auth, req):
    res = req.post(
        server.api("/album/delete"),
        headers=auth,
        json={"albumID": 4}
    )
    assert res.status_code == 404

def test_reorder_album_auth(server, req):
    res = req.post(
        server.api("/album/reorder/2"),
        json={"newOrdering": [5,4,3,2,1]}
    )
    assert res.status_code == 401

def test_reorder_album_not_enough(server, auth, req):
    res = req.post(
        server.api("/album/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,3]}
    )
    assert res.status_code == 400
    assert "Miscount" in res.json()["message"]

def test_reorder_album_not_enough(server, auth, req):
    res = req.post(
        server.api("/album/reorder/2"),
        headers=auth,
        json={"newOrdering": [7,6,5,4,3,2,1]}
    )
    assert res.status_code == 400
    assert "Miscount" in res.json()["message"]

def test_reorder_album_bad_index(server, auth, req):
    res = req.post(
        server.api("/album/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,3,2,7]}
    )
    assert res.status_code == 400
    assert "Misplaced" in res.json()["message"]

def test_reorder_album_non_unique(server, auth, req):
    res = req.post(
        server.api("/album/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,4,3,2]}
    )
    assert res.status_code == 400
    assert "Non-unique" in res.json()["message"]

def test_reorder_album(server, auth, req):
    res = req.post(
        server.api("/album/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,3,2,1]}
    )
    assert res.status_code == 200

    res = req.get(
        server.api("/album/view/2")
    )
    assert res.status_code == 200
    order = [p["id"] for p in res.json()["photos"]]
    assert order == [5,4,3,2,1]

def test_reorder_album_list_auth(server, req):
    res = req.post(
        server.api("/album/reorderList"),
        json={"newOrdering": [3,2,1]}
    )
    assert res.status_code == 401

def test_reorder_album_list_not_enough(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [2,1]}
    )
    assert res.status_code == 400
    assert "Miscount" in res.json()["message"]

def test_reorder_album_list_too_much(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [4,3,2,1]}
    )
    assert res.status_code == 400
    assert "Miscount" in res.json()["message"]

def test_reorder_album_list_bad_index(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [3,2,7]}
    )
    assert res.status_code == 400
    assert "Misplaced" in res.json()["message"]

def test_reorder_album_list_non_unique(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [3,2,2]}
    )
    assert res.status_code == 400
    assert "Non-unique" in res.json()["message"]

def test_reorder_album_list(server, auth, req):
    res = req.post(
        server.api("/album/reorderList"),
        headers=auth,
        json={"newOrdering": [3,2,1]}
    )
    assert res.status_code == 200

    res = req.get(
        server.api("/album/list"),
        headers=auth
    )
    assert res.status_code == 200
    print(res.json())
    order = [a["id"] for a in res.json()]
    assert order == [3,2,1]
