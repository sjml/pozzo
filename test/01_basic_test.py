import requests
import pytest

def test_connection(server, req):
    res = req.get(server.api("/index"))
    if res.status_code != 200:
        pytest.exit("Could not connect to local API server")
    assert res.status_code == 200

def test_404(server, req):
    res = req.get(server.api("/non-existent"))
    assert res.status_code == 404
