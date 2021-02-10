import os
import subprocess
import shutil
import time

import pytest

class PozzoServer:
    def __init__(self):
        baseDir = os.path.join(os.path.dirname(__file__), "..")
        self.baseURL = "0.0.0.0:8080"
        self.dbPath = os.path.join(baseDir, "pozzo.DB")
        self.imgPath = os.path.join(baseDir, "public", "img")
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
                "-t", os.path.join(baseDir, "public", "api")
            ],
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
        )

    def api(self, connStr):
        return f"http://{self.baseURL}/api{connStr}"

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

@pytest.fixture(autouse=True, scope="session")
def server():
    p = PozzoServer()
    time.sleep(2)
    yield p
    p.shutdown()
