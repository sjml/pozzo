import json
import textwrap

import requests

def stat_assert(res, code):
    if res.status_code != code:
        print(res.json())
    assert res.status_code == code

def get_img_path(size, phash, uniq, ext="jpg"):
    raw_dirs = textwrap.wrap(phash, 2)[:3]
    dirs = [d.replace("ad", "a_") for d in raw_dirs]
    return f"/photos/{'/'.join(dirs)}/{phash}_{uniq}_{size}.{ext}"

def create_album(title, groups, server, auth):
    res = requests.post(
        server.api("/album/new"),
        headers=auth,
        json={"title": title}
    )
    stat_assert(res, 200)
    album_id = res.json()["albumID"]

    res = requests.get(
        server.api(f"/album/view/{album_id}"),
    )
    default_gid = res.json()["photoGroups"][0]["id"]

    for gi, gd in enumerate(groups):
        if gi == 0:
            gid = default_gid
        else:
            res = requests.post(
                server.api("/group/new"),
                headers=auth,
                json={"albumID": album_id}
            )
            stat_assert(res, 200)
            gid = res.json()["groupID"]

        copy_insts = [{"photoID": x, "groupID": gid} for x in gd]
        res = requests.post(
            server.api("/photo/copy"),
            headers=auth,
            json={
                "copies": copy_insts
            }
        )
        stat_assert(res, 200)

    return album_id
