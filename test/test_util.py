import json
import textwrap

def stat_assert(res, code):
    if res.status_code != code:
        print(res.json())
    assert res.status_code == code

def get_img_path(size, phash, uniq, ext="jpg"):
    raw_dirs = textwrap.wrap(phash, 2)[:3]
    dirs = [d.replace("ad", "a_") for d in raw_dirs]
    return f"/photos/{'/'.join(dirs)}/{phash}_{uniq}_{size}.{ext}"
