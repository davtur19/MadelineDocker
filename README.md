# MadelineDocker

https://hub.docker.com/r/davtur19/madeline

## Run
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker run --name madeline -v "$(pwd)":/app/src/madeline -d madeline:latest
```

## Build
```bash
git clone https://github.com/davtur19/MadelineDocker
cd MadelineDocker
docker image build -t madeline:latest .
```
