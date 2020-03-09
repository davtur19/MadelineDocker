# MadelineDocker
[![Docker Image Size (tag)](https://img.shields.io/docker/image-size/davtur19/madeline/latest)](https://hub.docker.com/repository/docker/davtur19/madeline)
[![Docker Pulls](https://img.shields.io/docker/pulls/davtur19/madeline)](https://hub.docker.com/repository/docker/davtur19/madeline)

## Setup
- Install docker
- Set all values in env.list

## Run
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker/madeline
docker run --env-file ./env.list -v "$(pwd)":/app/src/madeline -it davtur19/madeline:latest
```

## Build
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker image build -t madeline:latest .
```
