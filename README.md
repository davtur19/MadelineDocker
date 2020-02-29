# MadelineDocker
![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/davtur19/madeline)
![Docker Pulls](https://img.shields.io/docker/pulls/davtur19/madeline)
![MicroBadger Size (tag)](https://img.shields.io/microbadger/image-size/davtur19/madeline/latest)

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
