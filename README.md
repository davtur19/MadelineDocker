# MadelineDocker
![Docker Image Size (tag)](https://img.shields.io/docker/image-size/davtur19/madeline/latest)
![Docker Pulls](https://img.shields.io/docker/pulls/davtur19/madeline)

## Setup
- Install docker
- Set all const in main.php

## Run
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker/madeline
docker run -v "$(pwd)":/app/src/madeline -it davtur19/madeline:latest
```

## Build
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker image build -t madeline:latest .
```
