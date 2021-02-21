# Dev Setup

## On a Mac:

1. `brew install imagemagick php@7.4 composer`
2. In the project directory: `composer install`
3. Have some version of Node.js around. 
4. In the frontend directory: `npm install`
5. You now **should** be able to run `scripts/serve.sh` from the project directory or `npm run dev` from the frontend and either should set you up with a working site on localhost:3000
    * Or maybe not. I may have forgotten something. 

## To test:

1. Have some version of Python 3 around. Probably use a virtualenv? You do you.
2. `pip install -r test/requirements.txt`
3. From the test directory: `pytest`
    * Note this tries to start up its on PHP server on the same port, so you can't be running the dev server at the same time. 
    * If it detects existing database or images it will move them out of the way to run its tests from a clean start and then move them back. (All the more reason not to be running the dev server at the same time.)


## To generate code coverage: 

1. Set `POZZO_COVERAGE=1` in your environment
2. From test directory: `pytest`
3. `test/tools/calculate_coverage.sh`
4. Open `output/html/index.html` using your favorite world wide web browser technology platform software.
