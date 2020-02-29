# MadelineDocker

https://hub.docker.com/r/davtur19/madeline

##run
```bash
docker run --name madeline -v "$(pwd)":/app/src/madeline -d madeline:latest
```

##build
```bash
docker image build -t madeline:1.0 .
```
