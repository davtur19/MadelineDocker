# MadelineDocker

TODO

docker image build -t madeline:1.0 .

docker run --name madeline -v "$(pwd)":/app/src/madeline -d madeline:1.0
