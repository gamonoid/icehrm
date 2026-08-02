## Setup Development Environment
```
git clone https://github.com/gamonoid/icehrm.git
cd icehrm
docker-compose up -d
```
- Visit [http://localhost:9180/](http://localhost:9180/) and login using `admin` as username and password.
- Watch this for more detailed instructions: [https://www.youtube.com/watch?v=sz8OV_ON6S8](https://www.youtube.com/watch?v=sz8OV_ON6S8)

### Extend IceHrm with custom Extensions
- Inorder to create an admin extension run
```
php ice create:extension sample admin
```

![](docs/images/icehrm-create-ext.gif)


- Refresh IceHrm to see a new menu item called `Sample Admin`
- The extension code can br found under `icehrm/extensions/sample/admin`
- Refer: [https://icehrm.com/explore/docs/extensions/](https://icehrm.com/explore/docs/extensions/) for more details.

### Building frontend assets

- When ever you have done a change to JavaScript or CSS files in icehrm/web you need to rebuild the frontend
- First make sure you have all the dependencies (just doing this once is enough)
```
cd icehrm/web
npm install
cd ..
npm install
```

- Build assets during development
```
gulp clean
gulp
```

- Build assets for production
```
gulp clean
gulp --eprod
```

- Build extensions
```
gulp ejs --xextension_name/admin
```

### Debugging code with psysh
You can run psysh inside the icehrm web docker container to manually debug the code.
- Start Psysh console
``` 
docker compose up -d
docker exec -it icehrm-icehrm-1 /bin/sh
./psysh -c ./.config/psysh/config.php
```
This will open a psysh console. You can instantiate any IceHrm class and debug it.
Here is an example of creating an employee object and loading an employee from the database.
```
$emp = new \Employees\Common\Model\Employee();
$emp->Load('id = ?',[1]);
var_dump($emp);
```
