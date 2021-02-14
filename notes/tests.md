Make a simple little Python test script that runs requests against the backend.
Have it test looking for every possible return code from the endpoints.

✅ -- check written and currently passing

backend functionality tests
    ✅ setup site (create user)
    ✅ cannot setup site again
    ✅ can't log in without credentials
    ✅ can't log in with wrong credentials
    ✅ can log in with right credentials
    ✅ login check serves new usable token
    can't upload without credentials
    upload 10 pictures
    should all be in unsorted
    create new album
    move 5 pictures to that album
    create another new album (set to private)
    move other 5 images to the private album
    "unsorted" album is empty now
    credentialed user can see album
    non-credentialed cannot
    reorder images in album
    star images
    check that they are in starred list
    delete individual image
        - no longer in album
    delete album
        - images move back to unsorted
    delete album + images
        - images are gone

perf/size
    don't let import time of realistic-sized album rise above X
    don't let lighthouse score of realistic-sized album drop below 90
    don't let javascript bundle size rise above X
