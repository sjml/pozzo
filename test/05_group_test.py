import os

import requests
import pytest

from test_util import stat_assert, get_img_path, create_album

@pytest.fixture(scope="module")
def aid(server, auth):
    return create_album("Group Testing Album", [list(range(2,12))], server, auth)

@pytest.fixture(scope="module")
def gid(server, auth, aid):
    res = requests.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    yield res.json()["photoGroups"][0]["id"]




def test_reorder_group_auth(server, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        json={"newOrdering": list(range(11,1,-1))}
    )
    stat_assert(res, 401)

def test_reorder_group_missing_params(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        headers=auth
    )
    stat_assert(res, 400)

def test_reorder_group_not_enough(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        headers=auth,
        json={"newOrdering": [5,4,3]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_group_too_much(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        headers=auth,
        json={"newOrdering": list(range(15,1,-1))}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_group_bad_index(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        headers=auth,
        json={"newOrdering": [12, 10, 9, 8, 7, 6, 5, 4, 3, 2]}
    )
    stat_assert(res, 400)
    assert "Misplaced" in res.json()["message"]

def test_reorder_group_non_unique(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        headers=auth,
        json={"newOrdering": [11, 11, 9, 8, 7, 6, 5, 4, 3, 2]}
    )
    stat_assert(res, 400)
    assert "Non-unique" in res.json()["message"]

def test_reorder_group_nonexistent(server, auth, req):
    res = req.post(
        server.api(f"/group/reorder/45"),
        headers=auth,
        json={"newOrdering": list(range(11,1,-1))}
    )
    stat_assert(res, 404)

def test_reorder_group(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/reorder/{gid}"),
        headers=auth,
        json={"newOrdering": list(range(11,1,-1))}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{gid}")
    )
    stat_assert(res, 200)
    gdata = res.json()["photoGroups"][0]
    order = [p["id"] for p in gdata["photos"]]
    assert order == [11,10,9,8,7,6,5,4,3,2]

def test_create_group_auth(server, auth, req, aid, gid):
    res = req.post(
        server.api("/group/new"),
        json={"albumID": aid, "fromGroup": gid, "photoIDs": [5, 11, 10, 9]}
    )
    stat_assert(res, 401)

def test_create_group_params(server, auth, req, aid, gid):
    res = req.post(
        server.api("/group/new"),
        headers=auth
    )
    stat_assert(res, 400)

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"fromGroup": gid, "photoIDs": [5, 11, 10, 9]}
    )
    stat_assert(res, 400)
    assert "Missing 'albumID'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": aid, "fromGroup": "A", "photoIDs": [5, 11, 10, 9]}
    )
    stat_assert(res, 400)
    assert "Invalid parameter 'fromGroup'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": aid, "fromGroup": gid}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'photoIDs'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": aid, "fromGroup": gid, "photoIDs": "all"}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'photoIDs'" in res.json()["message"]

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": aid, "fromGroup": gid, "photoIDs": [5, "eleven", 10, 9]}
    )
    stat_assert(res, 400)
    assert "Non-numeric value in 'photoIDs'" in res.json()["message"]

def test_create_empty_group(server, auth, req, aid):
    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": aid},
    )
    stat_assert(res, 200)
    ngid = res.json()["groupID"]

    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)

    adata = res.json()
    assert len(adata["photoGroups"]) == 2
    assert adata["photoGroups"][1]["id"] == ngid
    assert len(adata["photoGroups"][0]["photos"]) == 10
    assert len(adata["photoGroups"][1]["photos"]) == 0

def test_create_group_from_other(server, auth, req, aid, gid):
    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    assert len(res.json()["photoGroups"][0]["photos"]) == 10

    res = req.post(
        server.api("/group/new"),
        headers=auth,
        json={"albumID": aid, "fromGroup": gid, "photoIDs": [5, 11, 10, 9]}
    )
    stat_assert(res, 200)
    ngid = res.json()["groupID"]
    assert ngid != gid

    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    adata = res.json()
    assert len(adata["photoGroups"]) == 3
    g0 = adata["photoGroups"][0]
    g1 = adata["photoGroups"][1]
    g2 = adata["photoGroups"][2]
    assert len(g0["photos"]) == 6
    assert len(g1["photos"]) == 4
    assert len(g2["photos"]) == 0

    assert [g["id"] for g in g1["photos"]] == [11, 10, 9, 5]

def test_group_metadata_auth(server, req, gid):
    res = req.post(
        server.api(f"/group/edit/{gid}"),
    )
    stat_assert(res, 401)

def test_group_metadata_empty(server, auth, req, gid):
    res = req.post(
        server.api(f"/group/edit/{gid}"),
        headers=auth
    )
    stat_assert(res, 400)

def test_group_metadata_nonexistent(server, auth, req):
    res = req.post(
        server.api("/group/edit/17"),
        headers=auth,
        json={
            "description": "**New description for a group.**",
            "hasMap": 1,
        }
    )
    stat_assert(res, 404)

def test_group_metadata(server, auth, req, aid, gid):
    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    adata = res.json()
    gdata = adata["photoGroups"][0]
    assert gdata["id"] == gid

    assert adata["description"] == ""
    assert adata["hasMap"] == False

    res = req.post(
        server.api(f"/group/edit/{gid}"),
        headers=auth,
        json={
            "description": "**New description for a group.**",
            "hasMap": 1,
        }
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    adata = res.json()
    gdata = adata["photoGroups"][0]
    assert gdata["id"] == gid

    assert gdata["description"] == "**New description for a group.**"
    assert gdata["hasMap"] == True

def test_group_invalid_metadata(server, req, auth, gid):
    res = req.post(
        server.api(f"/group/edit/{gid}"),
        headers=auth,
        json={
            "description": 1337,
            "hasMap": "nah"
        }
    )
    stat_assert(res, 400)

@pytest.fixture(scope="module")
def mta(server, auth):
    return create_album("Move Target Album", [[2,3],[4,5]], server, auth)

def test_move_group_auth(server, req, auth, gid, mta):
    res = req.post(
        server.api(f"/group/move/{gid}"),
        json={"toAlbum": mta}
    )
    stat_assert(res, 401)

def test_move_nonexistent_group(server, req, auth, mta):
    res = req.post(
        server.api("/group/move/25"),
        headers=auth,
        json={"toAlbum": mta}
    )
    stat_assert(res, 404)
    assert "Group not found" in res.json()["message"]

def test_move_group_params(server, req, auth, gid):
    res = req.post(
        server.api(f"/group/move/{gid}"),
        headers=auth,
        json={}
    )
    stat_assert(res, 400)
    assert "Missing parameters" in res.json()["message"]

    res = req.post(
        server.api(f"/group/move/{gid}"),
        headers=auth,
        json={"toAlbumID": "three"}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'toAlbumID'" in res.json()["message"]

    res = req.post(
        server.api(f"/group/move/{gid}"),
        headers=auth,
        json={"toAlbumID": 25}
    )
    stat_assert(res, 404)
    assert "No such album" in res.json()["message"]

def test_move_group(server, req, auth, aid, gid, mta):
    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert gid in glist

    res = req.post(
        server.api(f"/group/move/{gid}"),
        headers=auth,
        json={"toAlbumID": mta}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert gid not in glist

    res = req.get(
        server.api(f"/album/view/{mta}"),
        headers=auth
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert gid in glist

def test_reorder_album_groups_auth(server, req, mta):
    # mta's groups at this point are [8,9,5]
    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        json={"newOrdering": [5,8,9]}
    )
    stat_assert(res, 401)

def test_reorder_album_groups_missing_params(server, auth, req, mta):
    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        headers=auth
    )
    stat_assert(res, 400)

def test_reorder_album_groups_not_enough(server, auth, req, mta):
    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        headers=auth,
        json={"newOrdering": [5,8]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_album_groups_too_much(server, auth, req, mta):
    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        headers=auth,
        json={"newOrdering": [4,5,8,9]}
    )
    stat_assert(res, 400)
    assert "Miscount" in res.json()["message"]

def test_reorder_album_groups_bad_index(server, auth, req, mta):
    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        headers=auth,
        json={"newOrdering": [6,8,9]}
    )
    stat_assert(res, 400)
    assert "Misplaced" in res.json()["message"]

def test_reorder_album_groups_non_unique(server, auth, req, mta):
    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        headers=auth,
        json={"newOrdering": [5,5,9]}
    )
    stat_assert(res, 400)
    assert "Non-unique" in res.json()["message"]

def test_reorder_album_groups_nonexistent(server, auth, req):
    res = req.post(
        server.api(f"/album/reorderGroups/1024"),
        headers=auth,
        json={"newOrdering": [5,8,9]}
    )
    stat_assert(res, 404)

def test_reorder_album_groups(server, auth, req, mta):
    res = req.get(
        server.api(f"/album/view/{mta}")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert glist == [8,9,5]

    res = req.post(
        server.api(f"/album/reorderGroups/{mta}"),
        headers=auth,
        json={"newOrdering": [5,8,9]}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{mta}")
    )
    stat_assert(res, 200)
    glist = [g["id"] for g in res.json()["photoGroups"]]
    assert glist == [5,8,9]

def test_merge_groups_auth(server, req):
    res = req.post(
        server.api("/group/merge/9"),
        json={"into": 8}
    )
    stat_assert(res, 401)

def test_merge_nonexistent_group(server, req, auth):
    res = req.post(
        server.api("/group/merge/17"),
        headers=auth,
        json={"into": 8}
    )
    stat_assert(res, 404)
    assert "Group not found" in res.json()["message"]

def test_merge_into_nonexistent_group(server, req, auth):
    res = req.post(
        server.api("/group/merge/9"),
        headers=auth,
        json={"into": 17}
    )
    stat_assert(res, 404)
    assert "Absorbing group not found" in res.json()["message"]

def test_merge_groups_params(server, auth, req):
    res = req.post(
        server.api("/group/merge/9"),
        headers=auth,
        json={}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'into'" in res.json()["message"]

    res = req.post(
        server.api("/group/merge/9"),
        headers=auth,
        json={"into": "eight"}
    )
    stat_assert(res, 400)
    assert "Missing or invalid parameter 'into'" in res.json()["message"]

    res = req.post(
        server.api("/group/merge/9"),
        headers=auth,
        json={"into": 2}
    )
    stat_assert(res, 400)
    assert "Merging groups must be in the same album" in res.json()["message"]

def test_merge_groups(server, auth, req):
    aid = create_album("Merge Test Album", [[5,6,7,8],[9,10,11]], server, auth)

    res = req.get(
        server.api(f"/album/view/{aid}")
    )
    stat_assert(res, 200)
    gids = [g["id"] for g in res.json()["photoGroups"]]

    res = req.get(
        server.api(f"/album/view/{aid}"),
    )
    stat_assert(res, 200)
    adata = res.json()

    assert len(adata["photoGroups"]) == 2
    g0 = adata["photoGroups"][0]
    g1 = adata["photoGroups"][1]
    assert len(g0["photos"]) == 4
    assert len(g1["photos"]) == 3
    g0ids = [p["id"] for p in g0["photos"]]
    g1ids = [p["id"] for p in g1["photos"]]
    assert g0ids == [5, 6, 7, 8]
    assert g1ids == [9, 10, 11]

    res = req.post(
        server.api(f"/group/merge/{g0['id']}"),
        headers=auth,
        json={"into": g1["id"]}
    )
    stat_assert(res, 200)

    res = req.get(
        server.api(f"/album/view/{aid}"),
    )
    stat_assert(res, 200)
    adata = res.json()

    assert len(adata["photoGroups"]) == 1
    gdata = adata["photoGroups"][0]
    assert len(gdata["photos"]) == 7
    gids = [p["id"] for p in gdata["photos"]]
    assert gids == [9, 10, 11, 5, 6, 7, 8]
