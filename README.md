# Pozzo

Because the world needed another web photo gallery. 

The backend is in PHP because it's easy and cheap to deploy almost anywhere. 

The PHP code is very slapdash. There is no ORM, so `db.php` has lots of copypasta. The router is homemade, and probably very shaky as well. At some point I may overcome my mental hurdle of using other libraries... I already did for the JWT stuff, at least. 

All in all, it should run reasonably fast, though. 

Secure? Need to do a pass on that kind of thing. 
