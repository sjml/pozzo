import os

import requests
import pytest

from test_util import stat_assert, get_img_path

def test_reorder_group_auth(server, req):
    res = req.post(
        server.api("/group/reorder/2"),
        json={"newOrdering": [5,4,3,2,1]}
    )
    stat_assert(res, 401)

def test_reorder_group_missing_params(server, auth, req):
    res = req.post(
        server.api("/group/reorder/2"),
        headers=auth
    )
    stat_assert(res, 400)

def test_reorder_group_not_enough(server, auth, req):
    res = req.post(
        server.api("/group/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,3]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_group_too_much(server, auth, req):
    res = req.post(
        server.api("/group/reorder/2"),
        headers=auth,
        json={"newOrdering": [7,6,5,4,3,2,1]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_group_bad_index(server, auth, req):
    res = req.post(
        server.api("/group/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,3,2,7]}
    )
    stat_assert(res, 400)
    assert "Misplaced" in res.json()["message"]

def test_reorder_group_non_unique(server, auth, req):
    res = req.post(
        server.api("/group/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,4,3,2]}
    )
    stat_assert(res, 400)
    assert "Non-unique" in res.json()["message"]

def test_reorder_group_nonexistent(server, auth, req):
    res = req.post(
        server.api("/group/reorder/7"),
        headers=auth,
        json={"newOrdering": [5,4,3,2,1]}
    )
    stat_assert(res, 404)

def test_reorder_group(server, auth, req):
    res = req.post(
        server.api("/group/reorder/2"),
        headers=auth,
        json={"newOrdering": [5,4,3,2,1]}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    gdata = res.json()["photoGroups"][0]
    order = [p["id"] for p in gdata["photos"]]
    assert order == [5,4,3,2,1]

def test_create_group_auth(server, auth, req):
    res = req.post(
        server.api("/group/new"),
        json={"albumID": 4, "fromGroup": 4, "photoIDs": [5, 12, 13]}
    )
    stat_assert(res, 401)

def test_create_group_params(server, auth, req):
    res = req.post(
        server.api("/group/new"),
        headers=auth
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"fromGroup": 4, "photoIDs": [5, 12, 13]}
    )
    stat_assert(res, 400)
    assert "Missing 'albumID'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": 4, "fromGroup": "A", "photoIDs": [5, 12, 13]}
    )
    stat_assert(res, 400)
    assert "Invalid parameter 'fromGroup'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": 4, "fromGroup": 4}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'photoIDs'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": 4, "fromGroup": 4, "photoIDs": "all"}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'photoIDs'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": 4, "fromGroup": 4, "photoIDs": [4, "twelve", 13]}
    )
    stat_assert(res, 400)
    assert "Non-numeric value in 'photoIDs'" in res.json()["message"]

def test_create_empty_group(server, auth, req):
    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": 2},
    )
    stat_assert(res, 200)
    assert res.json()["groupID"] == 5

    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)

    adata = res.json()
    assert len(adata["photoGroups"]) == 2
    assert adata["photoGroups"][1]["id"] == 5
    assert len(adata["photoGroups"][0]["photos"]) == 5
    assert len(adata["photoGroups"][1]["photos"]) == 0

def test_create_group_from_other(server, auth, req):
    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    assert len(res.json()["photoGroups"][0]["photos"]) == 5

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": 2, "fromGroup": 2, "photoIDs": [2, 3, 4]}
    )
    stat_assert(res, 200)
    assert res.json()["groupID"] == 6

    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    adata = res.json()
    print(adata)
    g0 = adata["photoGroups"][0]
    g1 = adata["photoGroups"][1]
    g2 = adata["photoGroups"][2]
    assert len(g0["photos"]) == 2
    assert len(g1["photos"]) == 3
    assert len(g2["photos"]) == 0


def test_group_metadata_auth(server, req):
    res = req.post(
        server.api("/group/edit/5"),
    )
    stat_assert(res, 401)

def test_group_metadata_empty(server, auth, req):
    res = req.post(
        server.api("/group/edit/5"),
        headers=auth
    )
    stat_assert(res, 400)

def test_group_metadata_nonexistent(server, auth, req):
    res = req.post(
        server.api("/group/edit/17"),
        headers=auth,
        json={
            "description": "**New description for a group.**",
            "showMap": 1,
        }
    )
    stat_assert(res, 404)

def test_group_metadata(server, auth, req):
    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    adata = res.json()
    gdata = adata["photoGroups"][1]
    assert gdata["id"] == 6

    assert adata["description"] == ""
    assert adata["showMap"] == False

    res = req.post(
        server.api("/group/edit/6"),
        headers=auth,
        json={
            "description": "**New description for a group.**",
            "showMap": 1,
        }
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    adata = res.json()
    gdata = adata["photoGroups"][1]
    assert gdata["id"] == 6

    assert gdata["description"] == "**New description for a group.**"
    assert gdata["showMap"] == True

def test_group_invalid_metadata(server, req, auth):
    res = req.post(
        server.api("/group/edit/2"),
        headers=auth,
        json={
            "description": 1337,
            "showMap": "nah"
        }
    )
    stat_assert(res, 400)

def test_move_group_auth(server, req, auth):
    res = req.post(
        server.api("/group/move/4"),
        json={"toAlbum": 3}
    )
    stat_assert(res, 401)

def test_move_nonexistent_group(server, req, auth):
    res = req.post(
        server.api("/group/move/25"),
        headers=auth,
        json={"toAlbum": 3}
    )
    stat_assert(res, 404)
    assert "Group not found" in res.json()["message"]

def test_move_group_params(server, req, auth):
    res = req.post(
        server.api("/group/move/4"),
        headers=auth,
        json={}
    )
    stat_assert(res, 400)
    assert "Missing parameters" in res.json()["message"]

    res = req.post(
        server.api("/group/move/4"),
        headers=auth,
        json={"toAlbumID": "three"}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'toAlbumID'" in res.json()["message"]

    res = req.post(
        server.api("/group/move/4"),
        headers=auth,
        json={"toAlbumID": 25}
    )
    stat_assert(res, 404)
    assert "No such album" in res.json()["message"]

def test_move_group(server, req, auth):
    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert 6 in glist

    res = req.post(
        server.api("/group/move/4"),
        headers=auth,
        json={"toAlbumID": 3}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert 4 not in glist

    res = req.get(
        server.api("/album/view/3"),
        headers=auth
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert 4 in glist

def test_reorder_album_groups_auth(server, req):
    res = req.post(
        server.api("/album/reorderGroups/2"),
        json={"newOrdering": [6,5,2]}
    )
    stat_assert(res, 401)

def test_reorder_album_groups_missing_params(server, auth, req):
    res = req.post(
        server.api("/album/reorderGroups/2"),
        headers=auth
    )
    stat_assert(res, 400)

def test_reorder_album_groups_not_enough(server, auth, req):
    res = req.post(
        server.api("/album/reorderGroups/2"),
        headers=auth,
        json={"newOrdering": [6,5]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_album_groups_too_much(server, auth, req):
    res = req.post(
        server.api("/album/reorderGroups/2"),
        headers=auth,
        json={"newOrdering": [7,6,5,4,3,2,1]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_album_groups_bad_index(server, auth, req):
    res = req.post(
        server.api("/album/reorderGroups/2"),
        headers=auth,
        json={"newOrdering": [6,5,1]}
    )
    stat_assert(res, 400)
    assert "Misplaced" in res.json()["message"]

def test_reorder_album_groups_non_unique(server, auth, req):
    res = req.post(
        server.api("/album/reorderGroups/2"),
        headers=auth,
        json={"newOrdering": [5,5,2]}
    )
    stat_assert(res, 400)
    assert "Non-unique" in res.json()["message"]

def test_reorder_album_groups_nonexistent(server, auth, req):
    res = req.post(
        server.api("/album/reorderGroups/12"),
        headers=auth,
        json={"newOrdering": [6,5,2]}
    )
    stat_assert(res, 404)

def test_reorder_album_groups(server, auth, req):
    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert glist == [2,6,5]

    res = req.post(
        server.api("/album/reorderGroups/2"),
        headers=auth,
        json={"newOrdering": [6,5,2]}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api("/album/view/2")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert glist == [6,5,2]

def test_merge_groups_auth(server, req):
    res = req.post(
        server.api("/group/merge/3"),
        json={"into": 4}
    )
    stat_assert(res, 401)

def test_merge_nonexistent_group(server, req, auth):
    res = req.post(
        server.api("/group/merge/17"),
        headers=auth,
        json={"into": 4}
    )
    stat_assert(res, 404)
    assert "Group not found" in res.json()["message"]

def test_merge_into_nonexistent_group(server, req, auth):
    res = req.post(
        server.api("/group/merge/3"),
        headers=auth,
        json={"into": 17}
    )
    stat_assert(res, 404)
    assert "Absorbing group not found" in res.json()["message"]

def test_merge_groups_params(server, auth, req):
    res = req.post(
        server.api("/group/merge/3"),
        headers=auth,
        json={}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'into'" in res.json()["message"]

    res = req.post(
        server.api("/group/merge/3"),
        headers=auth,
        json={"into": "three"}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'into'" in res.json()["message"]

    res = req.post(
        server.api("/group/merge/3"),
        headers=auth,
        json={"into": 2}
    )
    stat_assert(res, 400)
    assert "Merging groups must be in the same album" in res.json()["message"]

def test_merge_groups(server, auth, req):
    # in what should be more common practice across the test suite;
    #   I'm making an album just to play with this
    res = req.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": "Group Merge Test Album"}
    )
    stat_assert(res, 200)
    album_id = res.json()["albumID"]
    assert album_id == 5

    res = req.get(
        server.api(f"/album/view/{album_id}")
    )
    stat_assert(res, 200)
    group_id = res.json()["photoGroups"][0]["id"]
    assert group_id == 7

    copyInsts = [{"photoID": x, "groupID": group_id} for x in range(1,10)]
    res = req.post(
        server.api("/photo/copy"),
        headers=auth,
        json={
            "copies": copyInsts
        }
    )
    stat_assert(res, 200)

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": album_id, "fromGroup": group_id, "photoIDs": [2, 3, 4, 5]}
    )
    stat_assert(res, 200)
    assert res.json()["groupID"] == 8

    res = req.get(
        server.api(f"/album/view/{album_id}"),
    )
    stat_assert(res, 200)
    adata = res.json()

    assert len(adata["photoGroups"]) == 2
    g0 = adata["photoGroups"][0]
    g1 = adata["photoGroups"][1]
    assert len(g0["photos"]) == 5
    assert len(g1["photos"]) == 4
    g0ids = [p["id"] for p in g0["photos"]]
    g1ids = [p["id"] for p in g1["photos"]]
    assert g0ids == [1, 6, 7, 8, 9]
    assert g1ids == [2, 3, 4, 5]

    res = req.post(
        server.api(f"/group/merge/{g1['id']}"),
        headers=auth,
        json={"into": g0["id"]}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{album_id}"),
    )
    stat_assert(res, 200)
    adata = res.json()

    assert len(adata["photoGroups"]) == 1
    gdata = adata["photoGroups"][0]
    assert len(gdata["photos"]) == 9
    gids = [p["id"] for p in gdata["photos"]]
    assert gids == [1, 6, 7, 8, 9, 2, 3, 4, 5]
