import os

import requests
import pytest

from test_util import stat_assert, get_img_path

def test_video_upload(server, req, auth):
    with open("./test_corpus/video/Abstract.mp4", "rb") as f:
        res = req.post(
            server.api("/upload"),
            headers=auth,
            files={"mediaUp": ("Abstract.mp4", f, "video/quicktime")},
        )
    stat_assert(res, 200)

    pdata = res.json()
    ipath = get_img_path("orig", pdata["hash"], pdata["uniq"], "mp4")
    res = req.head(server.access(ipath))
    stat_assert(res, 200)

    ipath = get_img_path("orig", pdata["hash"], pdata["uniq"])
    res = req.head(server.access(ipath))
    stat_assert(res, 200)
