backend tests:
    reset site
    can't upload without credentials
    create user
    get credentials
    upload 10 pictures
    should all be in unsorted
    create new album
    move 5 pictures to that album
    create another new album (set to private)
    move other 5 images to the private album
    "unsorted" album is empty now
    credentialed user can see album
    non-credentialed cannot
    star images
    check that they are in starred list
    delete individual image
        - no longer in album
    delete album
        - images move back to unsorted
    delete album + images
        - images are gone
