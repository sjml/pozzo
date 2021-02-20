import json


def stat_assert(res, code):
    if res.status_code != code:
        print(res.json())
    assert res.status_code == code
