# MadelineDocker

## Setup
- Install docker
- Set all Define in main.php

## Run
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker run -v "$(pwd)":/app/src/madeline -d madeline:latest
```

## Build
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker image build -t madeline:latest .
```

## DockerHub
https://hub.docker.com/r/davtur19/madeline
