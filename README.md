# FlowerStuff
Website about flowers! Made as a school project.

The website may seem odd in some places, but it's made to fit the project requirements. One of those requirements was using no frameworks, which is why plain Javascript was used.

The backend was also required to be done in PHP, so I created an API in it. I used nginx to serve everything, and for some routing logic. The rest of the routing logic was written in the backend itself. 

There's a small sqlite database from which data is pulled (like Counties and Cities). To be able to run the demo easily, the database is embedded in the Docker image, but normally we should connect to an external database that is actually persisted.



There's an 'admin' account in the database which lets you view existing users. The user is `admin` and the password is `123456789`. The actual password isn't saved in the database, only its hash.

## Running the application
To run the application you just have to build the Docker image, and then run it.

Run `docker build . -t flowerstuff:latest` from this folder, then `docker run -p 8080:8080 flowerstuff:latest`, and you'll be able to find the website at `http://localhost:8080/` 
