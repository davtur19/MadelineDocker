# MadelineDocker
![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/davtur19/madeline)
![Docker Pulls](https://img.shields.io/docker/pulls/davtur19/madeline)
![Docker Image Size (tag)](https://img.shields.io/docker/image-size/davtur19/madeline/latest)

## Setup
- Install docker
- Set all define in main.php

## Run
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker/madeline
docker run -v "$(pwd)":/app/src/madeline -d madeline:latest
```

## Build
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker image build -t madeline:latest .
```
