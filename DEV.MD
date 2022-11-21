# Sender plugin development setup
**Requirements**
- Docker compose

**Steps:**
- create a `.env`
- copy `.env.example` to `.env`
- run `docker-compose up`

> After successfully run the docker container it'll install 4 versions of Prestashop and the **Sender** module will be there just go to the `backoffice` endpoint and install that module by searching **Sender**. The current project folder will be linked with PrestaShop's modules folder.

## Ports
The PrestaShop application will run on `http://localhost` with the follwing ports
- http://localhost:8016 (PrestaShop version 1.6)
- http://localhost:8171 (PrestaShop version 1.7.1)
- http://localhost:8172 (PrestaShop version 1.7.2)
- http://localhost:8173 (PrestaShop version 1.7.3)

## Database
The `mysql` database port will be exposed by the Docker at `3340`. So, you can connect at `localhost:3340` from any database client.

> For the `database` credentials just look at the `.env` file's `Database` section

## Backoffice
To login to the `backoffice` just go to the `/backoffice` endpoint followed by the endpoint port. The `backoffice` endpoint customizable by `PS_FOLDER_ADMIN` environment variable.

> For the `backoffice` credentials, check the value from the `.env` file's `PS_ADMIN_MAIL` and `PS_ADMIN_PASSWD`.