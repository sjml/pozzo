import os
import subprocess
import shutil
import time

import requests
import pytest


POZZO_CODE_COVERAGE = False
if "POZZO_CODE_COVERAGE" in os.environ:
    POZZO_CODE_COVERAGE = True
POZZO_CONF = "no-debug.php.ini"
if POZZO_CODE_COVERAGE:
    POZZO_CONF = "coverage.php.ini"
elif "POZZO_DEBUG" in os.environ:
    POZZO_CONF = "debug.php.ini"
PUBLIC_DIR = "public"
if "POZZO_DIST" in os.environ:
    PUBLIC_DIR = os.path.join("dist", "public")


class PozzoServer:
    def __init__(self):
        self.baseDir = os.path.join(os.path.dirname(__file__), "..")

        if POZZO_CODE_COVERAGE:
            os.rename(os.path.join(self.baseDir, PUBLIC_DIR, "api", "index.php"), os.path.join(self.baseDir, PUBLIC_DIR, "api", "index.real.php"))
            shutil.copy(os.path.join("tools", "alt_index.php"), os.path.join(self.baseDir, PUBLIC_DIR, "api", "index.php"))

        self.baseURL = "0.0.0.0:8080"
        self.dbPath = os.path.join(self.baseDir, "pozzo.DB")
        self.imgPath = os.path.join(self.baseDir, "public", "photos")
        self.movedDB = False
        if os.path.exists(self.dbPath):
            self.movedDB = True
            os.rename(self.dbPath, self.dbPath + ".bak")
        self.movedImgs = False
        if os.path.exists(self.imgPath):
            self.movedImgs = True
            os.rename(self.imgPath, self.imgPath + ".bak")
        self.process = subprocess.Popen(
            ["php",
                "-c", os.path.join(self.baseDir, "scripts", "configs", POZZO_CONF),
                "-S", self.baseURL,
                "-t", os.path.join(self.baseDir, PUBLIC_DIR)
            ],
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
        )

    def api(self, connStr):
        return f"http://{self.baseURL}/api{connStr}"

    def access(self, connStr):
        return f"http://{self.baseURL}/{connStr}"

    def shutdown(self):
        self.process.terminate()
        if self.movedImgs:
            if os.path.exists(self.imgPath):
                shutil.rmtree(self.imgPath)
            os.rename(self.imgPath + ".bak", self.imgPath)
        if self.movedDB:
            if os.path.exists(self.dbPath):
                os.remove(self.dbPath)
            os.rename(self.dbPath + ".bak", self.dbPath)
        if POZZO_CODE_COVERAGE:
            os.remove(os.path.join(self.baseDir, PUBLIC_DIR, "api", "index.php"))
            os.rename(os.path.join(self.baseDir, PUBLIC_DIR, "api", "index.real.php"), os.path.join(self.baseDir, PUBLIC_DIR, "api", "index.php"))


@pytest.fixture(scope="session")
def server():
    p = PozzoServer()
    time.sleep(5)
    yield p
    p.shutdown()


class ReqCounter:
    def __init__(self):
        self.session = requests.Session()
        self.req_count = 0

    def __getattr__(self, name):
        def method(*args, **kwargs):
            self.req_count += 1
            self.session.headers.update({"TestAssertIndex": f"{self.req_count}"})
            return getattr(self.session, name)(*args, **kwargs)
        return method

@pytest.fixture()
def req(request):
    rc = ReqCounter()
    test_name = f"{request.node.parent.name}__{request.node.originalname}"
    rc.session.headers.update({"TestName": test_name})
    return rc

@pytest.fixture(scope="module")
def auth(server):
    data = {"userName": "test_user", "password": "bad_password"}
    headers={"TestName": "login_fixture", "TestAssertIndex": "0"}
    res = requests.post(server.api("/login"), json=data, headers=headers)
    time.sleep(1)
    return {"Authorization": f"Bearer {res.json()['token']}"}
