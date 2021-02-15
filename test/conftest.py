import os
import subprocess
import shutil
import time

import requests
import pytest

class PozzoServer:
    def __init__(self):
        baseDir = os.path.join(os.path.dirname(__file__), "..")
        self.baseURL = "0.0.0.0:8080"
        self.dbPath = os.path.join(baseDir, "pozzo.DB")
        self.imgPath = os.path.join(baseDir, "public", "photos")
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
                "-c", os.path.join(baseDir, "scripts", "configs", "no-debug.php.ini"),
                "-S", self.baseURL,
                "-t", os.path.join(baseDir, "public")
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

@pytest.fixture(scope="session")
def server():
    p = PozzoServer()
    time.sleep(5)
    yield p
    p.shutdown()

@pytest.fixture(scope="module")
def auth(server):
    data = {"userName": "test_user", "password": "bad_password"}
    res = requests.post(server.api("/login"), json=data)
    time.sleep(1)
    return {"Authorization": f"Bearer {res.json()['token']}"}
