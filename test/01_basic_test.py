import requests
import pytest

from test_util import stat_assert

def test_connection(server, req):
    res = req.get(server.api("/index"))
    if res.status_code != 200:
        pytest.exit("Could not connect to local API server")
    stat_assert(res, 200)

def test_404(server, req):
    res = req.get(server.api("/non-existent"))
    stat_assert(res, 404)
